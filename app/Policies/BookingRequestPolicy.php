<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\BookingRequest;
use App\Models\User;

/**
 * Patient booking requests contain personal data, so access is limited:
 * - Owner & Front desk: see and handle all requests.
 * - Dentist: read-only, only requests assigned to them.
 * - Content editor: no access.
 * Only the owner may permanently delete a request (e.g. when a patient asks for their data to be erased).
 */
class BookingRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canManageBookings() || $user->role === Role::Dentist;
    }

    public function view(User $user, BookingRequest $booking): bool
    {
        if ($user->role->canViewAllBookings()) {
            return true;
        }

        return $user->role === Role::Dentist && $booking->dentist?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false; // Requests come from the website form.
    }

    public function update(User $user, BookingRequest $booking): bool
    {
        return $user->role->canManageBookings();
    }

    public function delete(User $user, BookingRequest $booking): bool
    {
        return $user->role === Role::Owner;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
