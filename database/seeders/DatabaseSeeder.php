<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'ahmed@bellahoptions.com'],
            [
                'name' => 'Aare Abefe',
                'password' => Hash::make('#Panaman247'),
                'role' => 'admin',
                'bio' => 'Platform administrator',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user ready: '.$admin->name.' ('.$admin->email.')');

        // Demo catalogue is for local/staging only — never seed it into production.
        if (! app()->isProduction()) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}
