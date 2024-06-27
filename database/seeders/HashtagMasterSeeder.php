<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HashtagMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $captions = [
            "#love",
            "#instagood",
            "#instagramers",
            "#instagramer",
            "#instagramanet",
            "#instagramdogs",
            "#instagramjapan",
            "#instagramcats",
            "#instalove",
            "#instamood",
            "#comment",
            "#shoutout",
            "#follow",
            "#f4f",
            "#followme",
            "#followforfollow",
            "#follow4follow",
            "#teamfollowback",
            "#followbackteam",
            "#followall",
            "#followalways",
            "#followback",
            "#liker",
            "#likes",
            "#l4l",
            "#likes4likes",
            "#likesforlikes",
            "#liketeam",
            "#likeback",
            "#likeall",
            "#likealways",
            "#pleasefollow",
            "#follows",
            "#follower",
            "#following",
            "#instalife",
            "#instalike",
            "#instapic",
            "#insta",
            "#instacool",
            "#instafollow",
            "#instamomen"

        ];

        foreach ($captions as $caption) {
            DB::table('hashtag_master')->insert([
                'name' => $caption,
            ]);
        }
    }
}
