<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;

class PostTypeSeeder extends Seeder
{
    public function run()
    {
        // Seed data for different post types
        $postTypes = [
            ['post_name' => 'Image'],
            ['post_name' => 'Video'],
            ['post_name' => 'Description'],
            ['post_name' => 'Link'],
            ['post_name' => 'Audio'],
            ['post_name' => 'Article']
        ];
        DB::table('post_type')->insert($postTypes);
    }
}
