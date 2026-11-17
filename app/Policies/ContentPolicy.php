<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Website content (pages, services, dentists, FAQs):
 * the Owner and Content editor can edit it; other roles can't see it in the admin panel.
 * System pages (home, contact, privacy, ...) can't be deleted.
 */
class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canEditContent();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->role->canEditContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canEditContent();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->role->canEditContent();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->role->canEditContent() && ! ($model->is_system ?? false);
    }

    public function deleteAny(User $user): bool
    {
        return $user->role->canEditContent();
    }

    public function reorder(User $user): bool
    {
        return $user->role->canEditContent();
    }
}
