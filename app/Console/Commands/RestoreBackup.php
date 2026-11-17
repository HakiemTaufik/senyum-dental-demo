<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Restores a backup made by spatie/laravel-backup into THIS site:
 * the database, and the uploaded files (photos).
 *
 *   php artisan app:restore-backup                      # newest backup of this site, from Backblaze
 *   php artisan app:restore-backup --from=senyum-production   # e.g. restore LIVE data into the test copy
 *   php artisan app:restore-backup --disk=local         # use the copy kept on the server
 *
 * Restoring over the live site requires --allow-production (disaster recovery only).
 */
class RestoreBackup extends Command
{
    protected $signature = 'app:restore-backup
        {--disk=b2 : Where to read the backup from (b2 or local)}
        {--from= : Which site\'s backups to use (default: this site\'s own)}
        {--file= : A specific backup file (default: the newest)}
        {--allow-production : Required to restore over the live site}
        {--force : Do not ask for confirmation}';

    protected $description = 'Restore the database and uploaded files from a backup';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('allow-production')) {
            $this->error('This is the LIVE site. Restoring overwrites all current data. Re-run with --allow-production if you are sure.');

            return self::FAILURE;
        }

        $disk = Storage::disk($this->option('disk'));
        $name = $this->option('from') ?: config('backup.backup.name');

        $file = $this->option('file') ?: collect($disk->files($name))
            ->filter(fn (string $path): bool => str_ends_with($path, '.zip'))
            ->sortByDesc(fn (string $path): int => $disk->lastModified($path))
            ->first();

        if (! $file) {
            $this->error("No backups found for \"{$name}\" on disk \"{$this->option('disk')}\".");

            return self::FAILURE;
        }

        $this->info("Backup: {$file}");
        $this->line('  made '.date('j M Y, g:i a', $disk->lastModified($file)).', '.round($disk->size($file) / 1024).' KB');
        $this->warn('This replaces ALL data in the database "'.config('database.connections.'.config('database.default').'.database').'" and the uploaded files of this site.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            return self::FAILURE;
        }

        $work = storage_path('app/restore-temp/'.now()->format('YmdHis'));
        File::ensureDirectoryExists($work);

        try {
            $zipPath = "{$work}/backup.zip";
            file_put_contents($zipPath, $disk->readStream($file));
            $this->extract($zipPath, "{$work}/files");

            $this->restoreDatabase("{$work}/files");
            $this->restoreUploads("{$work}/files");
            $this->resetUnreadableTwoFactor();

            Artisan::call('cache:clear');
            Artisan::call('optimize:clear');
            if (app()->isProduction() || app()->environment('staging')) {
                Artisan::call('optimize');
            }
        } finally {
            File::deleteDirectory($work);
        }

        $this->info('✓ Restore complete.');

        return self::SUCCESS;
    }

    private function extract(string $zipPath, string $target): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Could not open the backup file.');
        }

        if ($password = config('backup.backup.password')) {
            $zip->setPassword($password);
        }

        if (! $zip->extractTo($target)) {
            throw new RuntimeException('Could not unpack the backup. Is BACKUP_ARCHIVE_PASSWORD the same as when the backup was made?');
        }

        $zip->close();
    }

    private function restoreDatabase(string $dir): void
    {
        $dump = collect(File::allFiles($dir))->first(fn ($f): bool => str_contains($f->getPathname(), 'db-dumps'));

        if (! $dump) {
            throw new RuntimeException('The backup does not contain a database dump.');
        }

        $sqlPath = $dump->getPathname();
        if (str_ends_with($sqlPath, '.gz')) {
            $plain = substr($sqlPath, 0, -3);
            $in = gzopen($sqlPath, 'rb');
            $out = fopen($plain, 'wb');
            while (! gzeof($in)) {
                fwrite($out, gzread($in, 1024 * 512));
            }
            gzclose($in);
            fclose($out);
            $sqlPath = $plain;
        }

        $this->line('→ Restoring the database…');
        $connection = config('database.connections.'.config('database.default'));

        if ($connection['driver'] !== 'mysql' && $connection['driver'] !== 'mariadb') {
            throw new RuntimeException('Restoring is only supported for MySQL/MariaDB.');
        }

        if (! app()->isProduction()) {
            Artisan::call('db:wipe', ['--force' => true]);
        }

        // Credentials go in a private temporary file, so the password never appears in the process list.
        $credentials = dirname($sqlPath).'/my.cnf';
        file_put_contents($credentials, sprintf(
            "[client]\nuser=\"%s\"\npassword=\"%s\"\nhost=\"%s\"\nport=%d\n",
            $connection['username'], $connection['password'], $connection['host'], $connection['port'],
        ));
        chmod($credentials, 0600);

        $result = Process::timeout(600)
            ->input(fopen($sqlPath, 'rb'))
            ->run(['mysql', "--defaults-extra-file={$credentials}", $connection['database']]);

        if ($result->failed()) {
            throw new RuntimeException('Database import failed: '.$result->errorOutput());
        }

        DB::reconnect();
    }

    private function restoreUploads(string $dir): void
    {
        // The zip keeps each file's full original path, so look for the ".../storage/app/public" folder.
        $source = null;
        $folders = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
        );
        foreach ($folders as $item) {
            if ($item->isDir() && str_ends_with(str_replace('\\', '/', $item->getPathname()), 'storage/app/public')) {
                $source = $item->getPathname();
                break;
            }
        }

        if (! $source) {
            $this->line('→ No uploaded files in this backup.');

            return;
        }

        $this->line('→ Restoring uploaded files…');
        File::copyDirectory($source, storage_path('app/public'));
    }

    /**
     * Two-factor secrets are encrypted with the site's APP_KEY. When a backup is restored onto a
     * site with a different key (e.g. live data into the test copy), they can't be read, so those
     * staff members simply set up two-factor login again at their next sign-in.
     */
    private function resetUnreadableTwoFactor(): void
    {
        $reset = 0;

        DB::table('users')->whereNotNull('app_authentication_secret')->orderBy('id')->each(function ($user) use (&$reset): void {
            try {
                Crypt::decryptString($user->app_authentication_secret);
            } catch (DecryptException) {
                User::whereKey($user->id)->toBase()->update([
                    'app_authentication_secret' => null,
                    'app_authentication_recovery_codes' => null,
                ]);
                $reset++;
            }
        });

        if ($reset > 0) {
            $this->warn("→ {$reset} staff member(s) will set up two-factor login again (different encryption key on this site).");
        }
    }
}
