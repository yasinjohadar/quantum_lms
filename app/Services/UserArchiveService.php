<?php

namespace App\Services;

use App\Models\User;
use App\Models\ArchivedUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserArchiveService
{
    /**
     * Archive a single user
     */
    public function archive(User $user, ?string $reason = null, ?User $archivedBy = null): ArchivedUser
    {
        return DB::transaction(function () use ($user, $reason, $archivedBy) {
            // Get user attributes before any changes
            $userAttributes = $user->fresh()->getAttributes();
            $userId = $user->id;
            
            // Mark user as archived first
            $user->update([
                'is_archived' => true,
                'archived_at' => now(),
            ]);

            // Create archived user record BEFORE soft delete
            // This ensures the foreign key constraint is satisfied
            $archivedUser = ArchivedUser::create([
                'original_user_id' => $userId,
                'name' => $userAttributes['name'] ?? $user->name,
                'email' => $userAttributes['email'] ?? $user->email,
                'phone' => $userAttributes['phone'] ?? $user->phone,
                'password' => $userAttributes['password'] ?? $user->password,
                'email_verified_at' => $userAttributes['email_verified_at'] ?? $user->email_verified_at,
                'avatar' => $userAttributes['avatar'] ?? $user->avatar ?? null,
                'student_id' => $userAttributes['student_id'] ?? null,
                'date_of_birth' => $userAttributes['date_of_birth'] ?? null,
                'gender' => $userAttributes['gender'] ?? null,
                'last_login_at' => $userAttributes['last_login_at'] ?? $user->last_login_at,
                'last_login_ip' => $userAttributes['last_login_ip'] ?? $user->last_login_ip,
                'last_device_type' => $userAttributes['last_device_type'] ?? null,
                'is_active' => $userAttributes['is_active'] ?? $user->is_active ?? false,
                'is_connected' => $userAttributes['is_connected'] ?? false,
                'address' => $userAttributes['address'] ?? null,
                'archived_at' => now(),
                'archived_by' => $archivedBy?->id ?? auth()->id(),
                'archive_reason' => $reason,
            ]);

            // Soft delete the user AFTER creating archived record
            // Soft delete doesn't actually remove the record, so foreign key is still valid
            $user->delete();

            Log::info("User archived", [
                'user_id' => $userId,
                'archived_user_id' => $archivedUser->id,
                'archived_by' => $archivedBy?->id ?? auth()->id(),
            ]);

            return $archivedUser;
        });
    }

    /**
     * Restore a single archived user
     */
    public function restore(ArchivedUser $archivedUser, ?User $restoredBy = null): User
    {
        return DB::transaction(function () use ($archivedUser, $restoredBy) {
            // Restore user (using original_user_id to maintain relationships)
            $user = User::withTrashed()->find($archivedUser->original_user_id);
            
            if ($user) {
                // Restore soft deleted user
                $user->restore();
                
                // Update user data from archived record
                $user->update([
                    'name' => $archivedUser->name,
                    'email' => $archivedUser->email,
                    'phone' => $archivedUser->phone,
                    'avatar' => $archivedUser->avatar,
                    'student_id' => $archivedUser->student_id,
                    'date_of_birth' => $archivedUser->date_of_birth,
                    'gender' => $archivedUser->gender,
                    'last_login_at' => $archivedUser->last_login_at,
                    'last_login_ip' => $archivedUser->last_login_ip,
                    'last_device_type' => $archivedUser->last_device_type,
                    'is_active' => $archivedUser->is_active,
                    'address' => $archivedUser->address,
                    'is_archived' => false,
                    'archived_at' => null,
                ]);
            } else {
                // Create new user if original was permanently deleted
                $user = User::create([
                    'name' => $archivedUser->name,
                    'email' => $archivedUser->email,
                    'phone' => $archivedUser->phone,
                    'password' => $archivedUser->password,
                    'email_verified_at' => $archivedUser->email_verified_at,
                    'avatar' => $archivedUser->avatar,
                    'student_id' => $archivedUser->student_id,
                    'date_of_birth' => $archivedUser->date_of_birth,
                    'gender' => $archivedUser->gender,
                    'last_login_at' => $archivedUser->last_login_at,
                    'last_login_ip' => $archivedUser->last_login_ip,
                    'last_device_type' => $archivedUser->last_device_type,
                    'is_active' => $archivedUser->is_active,
                    'address' => $archivedUser->address,
                    'is_archived' => false,
                ]);
            }

            // Mark archived user as restored
            $archivedUser->update([
                'restored_at' => now(),
                'restored_by' => $restoredBy?->id ?? auth()->id(),
            ]);

            Log::info("User restored", [
                'archived_user_id' => $archivedUser->id,
                'user_id' => $user->id,
                'restored_by' => $restoredBy?->id ?? auth()->id(),
            ]);

            return $user;
        });
    }

    /**
     * Archive multiple users
     */
    public function bulkArchive(array $userIds, ?string $reason = null, ?User $archivedBy = null): array
    {
        $archivedUsers = [];
        $errors = [];

        foreach ($userIds as $userId) {
            try {
                $user = User::find($userId);
                
                if (!$user) {
                    $errors[] = [
                        'user_id' => $userId,
                        'error' => 'المستخدم غير موجود',
                    ];
                    continue;
                }
                
                if ($user->is_archived) {
                    $errors[] = [
                        'user_id' => $userId,
                        'error' => 'المستخدم مؤرشف بالفعل',
                    ];
                    continue;
                }
                
                // التحقق من أن المستخدم ليس soft deleted
                if ($user->trashed()) {
                    $errors[] = [
                        'user_id' => $userId,
                        'error' => 'المستخدم محذوف بالفعل',
                    ];
                    continue;
                }
                
                $archivedUsers[] = $this->archive($user, $reason, $archivedBy);
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ];
                Log::error("Failed to archive user", [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'archived' => $archivedUsers,
            'errors' => $errors,
        ];
    }

    /**
     * Restore multiple archived users
     */
    public function bulkRestore(array $archivedUserIds, ?User $restoredBy = null): array
    {
        $restoredUsers = [];
        $errors = [];

        foreach ($archivedUserIds as $archivedUserId) {
            try {
                $archivedUser = ArchivedUser::find($archivedUserId);
                if ($archivedUser && !$archivedUser->restored_at) {
                    $restoredUsers[] = $this->restore($archivedUser, $restoredBy);
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'archived_user_id' => $archivedUserId,
                    'error' => $e->getMessage(),
                ];
                Log::error("Failed to restore user", [
                    'archived_user_id' => $archivedUserId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'restored' => $restoredUsers,
            'errors' => $errors,
        ];
    }
}
