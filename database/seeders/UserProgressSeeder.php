<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
class UserProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        foreach (User::all() as $user) {
            foreach (Level::inRandomOrder()->limit(5)->get() as $level) {
                DB::table('user_progress')->insert([
                    'user_id' => $user->id,
                    'level_id' => $level->id,
                    'is_completed' => rand(0, 1),
                    'score' => rand(10, 100),
                    'attempts' => rand(1, 5),
                    'last_updated' => now(),
                ]);
            }
        }
    }

}
