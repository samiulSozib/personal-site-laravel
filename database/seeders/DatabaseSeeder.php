<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Creates the admin login and loads the starter content.
     *
     * Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding production. Re-running is safe:
     * records are matched by slug and updated in place.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'Site Admin',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'is_admin' => true,
            ],
        );

        $this->call(ContentSeeder::class);
    }
}
