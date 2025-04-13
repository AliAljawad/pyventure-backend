<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;
class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */


    public function run(): void
    {
        Achievement::factory()->count(8)->create();
    }

}
