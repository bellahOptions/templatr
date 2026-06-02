<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user only
        User::create([
            'name' => 'Aare Abefe',
            'email' => 'ahmed@bellahoptions.com',
            'password' => Hash::make('#Panaman247'),
            'role' => 'admin',
            'bio' => 'Platform administrator',
        ]);

        $this->command->info('Admin user created: Aare Abefe (ahmed@bellahoptions.com)');
    }
}
