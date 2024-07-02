<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;
use App\Models\UserDetails;
use  App\Models\CommunityDetail;
use App\Models\Follows;
use GuzzleHttp\Client;
use App\Models\CommunityArti;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ArtiNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arti:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications before Arti time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationController = new NotificationController(); 

        $communityDetailId = CommunityArti::whereNotNull('live_arti_link')->where('live_arti_link', '!=', '')->pluck('community_detail_id')->toArray();
        $artiIds = CommunityDetail::whereIn('id', $communityDetailId)->pluck('profile_id')->toArray();
        $followedIds = Follows::whereIn('followed_profile_id', $artiIds)->pluck('followed_profile_id')->toArray();
        $uniqueFollowedIds = array_unique($followedIds);
        $communitydetails = CommunityDetail::whereIn('profile_id', $uniqueFollowedIds)->with('communityArti')->get();

        $errors = [];
        foreach ($communitydetails as $communitydetail) {
            $communityName = str_replace(' ', '', $communitydetail->name_of_community);
            $artiURL = $communitydetail->communityArti->live_arti_link ?? null;
            if ($artiURL) {
                $notification = [
                    "title" => "ISHTDEV Notification",
                    "body" => "Click to join Arti of". $communitydetail->name_of_community,
                ];
                $data = [
                    "url" => $artiURL,
                    "message_body" => "Live Aarti of " . $communitydetail->name_of_community,
                    "contentType" => "VIDEO", 
                    "source" => "Youtube", 
                    "notication" => "true",
                    "text" => "sample message"
                ];
                $response = $notificationController->sendNotificationToAll($communityName, $notification, $data);
                
            }
        }      
    }
}
