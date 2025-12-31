<?php

namespace Database\Seeders;

use App\Models\Novel;
use App\Models\PenName;
use App\Models\User;
use Illuminate\Database\Seeder;

class NovelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create a default pen name for each user
            $penName = PenName::factory()->default()->create([
                'user_id' => $user->id,
            ]);

            // Create 2-4 novels per user
            Novel::factory()
                ->count(fake()->numberBetween(2, 4))
                ->create([
                    'user_id' => $user->id,
                    'pen_name_id' => $penName->id,
                ]);
        }
    }
}
