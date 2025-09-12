<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create wallet for admin
        $admin->getOrCreateWallet();

        // Give all permissions to super admin
        $permissionService = new PermissionService();
        foreach (array_keys(PermissionService::PERMISSIONS) as $permission) {
            $permissionService->givePermissionToAdmin($admin, $permission);
        }

        // Generate a referral code for admin
        $admin->referralCodes()->create([
            'code' => 'ADMIN2024',
            'max_usage' => null, // unlimited
            'expires_at' => null, // never expires
        ]);
    }
}
