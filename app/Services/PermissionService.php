<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Permission;

class PermissionService
{
    const PERMISSIONS = [
        'can_accept_withdrawals' => 'Can accept withdrawal requests',
        'can_reject_withdrawals' => 'Can reject withdrawal requests',
        'can_accept_topups' => 'Can accept top-up requests',
        'can_reject_topups' => 'Can reject top-up requests',
        'can_manage_admins' => 'Can manage admin accounts',
        'can_view_all_transactions' => 'Can view all transactions',
        'can_generate_referral_codes' => 'Can generate referral codes',
    ];

    public function seedPermissions(): void
    {
        foreach (self::PERMISSIONS as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }

    public function givePermissionToAdmin(Admin $admin, string $permission): void
    {
        if (!array_key_exists($permission, self::PERMISSIONS)) {
            throw new \Exception("Invalid permission: {$permission}");
        }

        $admin->givePermission($permission);
    }

    public function removePermissionFromAdmin(Admin $admin, string $permission): void
    {
        $admin->removePermission($permission);
    }

    public function getAdminPermissions(Admin $admin): array
    {
        return $admin->permissions->pluck('name')->toArray();
    }

    public function canProcessWithdrawals(Admin $admin): bool
    {
        return $admin->hasPermission('can_accept_withdrawals') ||
               $admin->hasPermission('can_reject_withdrawals');
    }

    public function canProcessTopups(Admin $admin): bool
    {
        return $admin->hasPermission('can_accept_topups') ||
               $admin->hasPermission('can_reject_topups');
    }

    public function canAcceptWithdrawals(Admin $admin): bool
    {
        return $admin->hasPermission('can_accept_withdrawals');
    }

    public function canRejectWithdrawals(Admin $admin): bool
    {
        return $admin->hasPermission('can_reject_withdrawals');
    }

    public function canAcceptTopups(Admin $admin): bool
    {
        return $admin->hasPermission('can_accept_topups');
    }

    public function canRejectTopups(Admin $admin): bool
    {
        return $admin->hasPermission('can_reject_topups');
    }
}
