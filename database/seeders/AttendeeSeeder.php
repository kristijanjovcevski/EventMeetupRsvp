<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attendee;
use Illuminate\Database\Seeder;

class AttendeeSeeder extends Seeder
{
    public function run(): void
    {

        $named = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob',   'email' => 'bob@example.com'],
            ['name' => 'Carol', 'email' => 'carol@example.com'],
        ];

        foreach ($named as $a) {
            Attendee::firstOrCreate(
                ['email' => $a['email']],
                ['name'  => $a['name']],
            );
        }
        Attendee::factory()->count(7)->create();
    }
}
