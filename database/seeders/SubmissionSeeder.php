<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Level;
use Illuminate\Support\Facades\DB;
class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        foreach (User::all() as $user) {
            foreach (Level::inRandomOrder()->limit(5)->get() as $level) {
                DB::table('submissions')->insert([
                    'user_id' => $user->id,
                    'level_id' => $level->id,
                    'code' => 'print("Submission attempt")',
                    'is_correct' => rand(0, 1),
                    'submitted_at' => now(),
                ]);
            }
        }
    }

}
