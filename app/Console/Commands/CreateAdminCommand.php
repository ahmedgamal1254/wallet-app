<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Services\PermissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create {--email=} {--password=} {--name=}';
    protected $description = 'Create a new admin user';

    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Admin email');
        $name = $this->option('name') ?: $this->ask('Admin name');
        $password = $this->option('password') ?: $this->secret('Admin password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("  - $error");
            }
            return 1;
        }

        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Create wallet
        $admin->getOrCreateWallet();

        $this->info("Admin created successfully!");
        $this->table(['ID', 'Name', 'Email'], [[$admin->id, $admin->name, $admin->email]]);

        if ($this->confirm('Give all permissions to this admin?', true)) {
            $permissionService = new PermissionService();
            foreach (array_keys(PermissionService::PERMISSIONS) as $permission) {
                $permissionService->givePermissionToAdmin($admin, $permission);
            }
            $this->info('All permissions granted!');
        }

        return 0;
    }
}
