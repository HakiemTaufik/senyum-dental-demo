<x-filament-panels::page>
    <x-filament::section heading="Find a person's data" description="Search by the email address they used on the website.">
        <form wire:submit="search">
            {{ $this->form }}
            <div style="margin-top: 1rem">
                <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">Search</x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if ($searchedEmail !== null)
        <x-filament::section :heading="'Results for '.$searchedEmail">
            @if ($this->records->isEmpty())
                <p>No appointment requests found for this email. Old requests are deleted automatically after the retention period.</p>
            @else
                <div style="overflow-x: auto">
                    <table style="width: 100%; text-align: left; font-size: 0.875rem; border-collapse: collapse">
                        <thead>
                            <tr style="border-bottom: 1px solid rgb(128 128 128 / 0.3)">
                                <th style="padding: 0.5rem 1rem 0.5rem 0">Reference</th>
                                <th style="padding: 0.5rem 1rem 0.5rem 0">Received</th>
                                <th style="padding: 0.5rem 1rem 0.5rem 0">Name</th>
                                <th style="padding: 0.5rem 1rem 0.5rem 0">Service</th>
                                <th style="padding: 0.5rem 1rem 0.5rem 0">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->records as $record)
                                <tr style="border-bottom: 1px solid rgb(128 128 128 / 0.15)">
                                    <td style="padding: 0.5rem 1rem 0.5rem 0; font-family: monospace">{{ $record->reference }}</td>
                                    <td style="padding: 0.5rem 1rem 0.5rem 0">{{ $record->created_at->format('j M Y') }}</td>
                                    <td style="padding: 0.5rem 1rem 0.5rem 0">{{ $record->name }}</td>
                                    <td style="padding: 0.5rem 1rem 0.5rem 0">{{ $record->serviceName() }}</td>
                                    <td style="padding: 0.5rem 1rem 0.5rem 0">{{ $record->status->getLabel() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem">
                    {{ $this->exportAction }}
                    {{ $this->deleteAction }}
                </div>
            @endif
        </x-filament::section>
    @endif

    <x-filament::section heading="How to handle a privacy request" collapsible collapsed>
        <ol style="list-style: decimal; padding-left: 1.25rem; font-size: 0.875rem; line-height: 1.7">
            <li><strong>Confirm who they are.</strong> Only act on requests from the email address on record, or after checking ID at the clinic.</li>
            <li><strong>Reply within 21 days</strong> (PDPA). Note the date the request arrived.</li>
            <li><strong>Copy of data:</strong> search above, click "Download a copy", and email the file to the person.</li>
            <li><strong>Correction:</strong> open the request under Bookings and update it, or ask them to send a new request.</li>
            <li><strong>Deletion / withdraw consent:</strong> click "Delete all of this person's booking data". Then check the clinic mailbox for old emails about them and delete those too.</li>
            <li><strong>Reply to confirm</strong> what you did. Every export and deletion is recorded in the activity log.</li>
        </ol>
        <p style="margin-top: 1rem; font-size: 0.875rem">Suspect a data breach (e.g. a lost phone with admin access, a hacked account)? Follow the breach steps in the handover guide immediately: the Commissioner must be notified within 72 hours.</p>
    </x-filament::section>
</x-filament-panels::page>
