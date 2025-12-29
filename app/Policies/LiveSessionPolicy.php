<?php

namespace App\Policies;

use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LiveSessionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LiveSession $liveSession): bool
    {
        // Admin and teachers can view all
        if ($user->hasAnyRole(['admin', 'teacher'])) {
            return true;
        }

        // Students can view if enrolled
        if ($user->hasRole('student')) {
            return $liveSession->canJoin($user);
        }

        return false;
    }

    /**
     * Determine whether the user can manage Zoom for this session
     */
    public function manageZoom(User $user, LiveSession $liveSession): bool
    {
        // Only admin and teachers can manage Zoom
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can join the session
     */
    public function join(User $user, LiveSession $liveSession): Response
    {
        // Only students can join
        if (!$user->hasRole('student')) {
            return Response::deny('Only students can join live sessions.');
        }

        // Check if user is enrolled
        if (!$liveSession->canJoin($user)) {
            return Response::deny('You are not enrolled in this session.');
        }

        // Check if user is active
        if (!$user->is_active) {
            return Response::deny('Your account is inactive.');
        }

        // Check time window
        if (!$liveSession->isWithinTimeWindow()) {
            return Response::deny('Session is not available for joining at this time.');
        }

        // Check if session is cancelled
        if ($liveSession->status === 'cancelled') {
            return Response::deny('This session has been cancelled.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LiveSession $liveSession): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LiveSession $liveSession): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LiveSession $liveSession): bool
    {
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LiveSession $liveSession): bool
    {
        return $user->hasRole('admin');
    }
}
