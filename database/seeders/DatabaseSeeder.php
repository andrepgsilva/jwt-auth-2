<?php

namespace Database\Seeders;

use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        PostCategoryFactory::new([
            'name' => 'Movies'
        ])->create();

        PostCategoryFactory::new([
            'name' => 'TV Shows'
        ])->create();

        PostCategoryFactory::new([
            'name' => 'Anime'
        ])->create();

        PostCategoryFactory::new([
            'name' => 'Games'
        ])->create();

        PostCategoryFactory::new([
            'name' => 'Other'
        ])->create();
    }
}
