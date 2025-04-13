<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Achievement;
class UserAchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        $users = User::all();
        $achievements = Achievement::all();

        foreach ($users as $user) {
            $user->achievements()->attach(
                $achievements->random(rand(1, 4))->pluck('id')->toArray(),
                ['earned_at' => now()]
            );
        }
    }

}
