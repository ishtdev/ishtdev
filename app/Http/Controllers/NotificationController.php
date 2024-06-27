<?php

namespace App\Http\Controllers;

use App\Models\CommunityBadge;
use App\Models\CommunityDetail;
use App\Models\CommunityHistory;
use App\Models\Follows;
use App\Models\Post;
use App\Models\PostData;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserCheckIn;
use Google\Auth\OAuth2;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private function processObject($object)
    {
        $fieldsToRemove = ['password', 'created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
        if (!empty($object)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }
        }
        return $object;
    }

    public function createChannel($communityName, $registrationTokens)
    {

        $to = "/topics/$communityName";
        $body = [
            'to' => $to,
            'registration_tokens' => $registrationTokens,
        ];
        $bodyJson = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=AAAAPtLGuM0:APA91bFJ7fP6ntGULVnWEDvOiaX2bx3wRI_2F2BmC8MJpD2OQxwzjLXoXwPQgE-NUJzIS1aSH5AUsknzhFdpcWL6toa9GNLKDctr_EggKo9vipBLminPm5o61dYLPVD8qLb6qFev_5jb',
        ];
        $client = new Client();
        try {
            $response = $client->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                'headers' => $headers,
                'body' => $bodyJson,
            ]);
            return $response->getBody();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeDevice($communityName, $registrationTokens)
    {
        $to = "/topics/$communityName";
        $body = [
            'to' => $to,
            'registration_tokens' => $registrationTokens,
        ];
        $bodyJson = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'key=AAAAPtLGuM0:APA91bFJ7fP6ntGULVnWEDvOiaX2bx3wRI_2F2BmC8MJpD2OQxwzjLXoXwPQgE-NUJzIS1aSH5AUsknzhFdpcWL6toa9GNLKDctr_EggKo9vipBLminPm5o61dYLPVD8qLb6qFev_5jb',
        ];
        $client = new Client();

        try {
            $response = $client->post('https://iid.googleapis.com/iid/v1:batchRemove', [
                'headers' => $headers,
                'body' => $bodyJson,
            ]);
            return $response->getBody();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateFCM(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
                'registration_tokens' => 'required',
            ]);
            $Token = $request->input('registration_tokens')[0];
            $Token = trim($Token, '"');

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }
            $userId = Profile::select('user_id')->where('id', $request->profile_id)->first();

            if (!$userId) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                ], 404);
            }
            $existingKey = User::where('id', $userId->user_id)->first();

            if (!$existingKey->device_key || $Token !== $existingKey->device_key) {
                $existingKey->update(['device_key' => $Token]);
                //-------update device key in community channel start----------
                $notificationController = new NotificationController();

                $followedIds = Follows::where('following_profile_id', $request->profile_id)->pluck('followed_profile_id');
                $followedCommunities = Profile::whereIn('id', $followedIds)->where('user_type_id', '3')->get();
                $registrationTokens = $request->input('registration_tokens');

                foreach ($followedCommunities as $community) {
                    $communityName = str_replace(' ', '', $community->communityDetail->name_of_community);
                    $response = $notificationController->createChannel($communityName, $registrationTokens);
                    echo $response;
                }

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'device key updated successfully',
                ], 200);

                //------update device key in community channel end----------
            } else {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'FCM key is the same',
                    'data' => [
                        'user_id' => $userId->user_id,
                        'profile_id' => $request->profile_id,
                        'device_key' => $Token,
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }

    public function sendNotificationToOne($to, $notification, $data)
    {  
        $credentialsFilePath = public_path('firebase/fcm.json');
        if (!file_exists($credentialsFilePath)) {
             throw new Exception('Service account credentials file not found at ' . $credentialsFilePath);
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        // Fetch access token
        $token = $client->fetchAccessTokenWithAssertion();
        
        // $client->refreshTokenWithAssertion();
        // $token = $client->getAccessToken();
        $access_token = $token['access_token'];
    
        if (isset($token['error'])) {
            return response()->json(['error' => $token['error']], 500);
        }
        $accessToken = $token['access_token'];

        $apiUrl = 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send';

        // Prepare notification payload
        $message = [
            'message' => [
                'token' => $to,
                'notification' => $notification,
                'data' => $data,
            ],
        ];
      
        // Make HTTP POST request to FCM API
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $message);
            
            // Return the response
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function sendNotificationToAll($communityName, $notification, $data)
    {
        $credentialsFilePath = public_path('firebase/fcm.json');
        
        // Ensure the file exists
        if (!file_exists($credentialsFilePath)) {
            return response()->json(['error' => 'Firebase credentials file not found'], 500);
        }
        
        // Initialize Google Client
        $client = new GoogleClient();
        try {
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error setting up Google Client: ' . $e->getMessage()], 500);
        }
       

        // Fetch access token
        try {
            
            $token = $client->fetchAccessTokenWithAssertion();
            if (isset($token['error'])) {
                return response()->json(['error' => $token['error']], 500);
            }
            $accessToken = $token['access_token'];
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching access token: ' . $e->getMessage()], 500);
        }

        // Set API URL for sending messages
        $apiUrl = 'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send';

        // Prepare notification payload
        $message = [
            'message' => [
                'topic' => $communityName,
                'notification' => $notification,
                'data' => $data,
                'android' => [
                    'priority' => 'high',
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'priority' => '10',
                        ],
                    ],
                ],
            ],
        ];

        // Make HTTP POST request to FCM API
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $message);

            // Return the response
          
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_type' => 'nullable',
                'post_data' => 'nullable',
                'city' => 'nullable',
                'profile_id' => 'required',
                'title' => 'required',
                'body' => 'required',
                'message_body' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $validatedData = $request->only(['post_type', 'post_data', 'profile_id', 'title', 'body', 'message_body']);

            $post = new Post();
            $post->post_type = $validatedData['post_type'];
            $post->city = isset($request->city) ? $request->city : null;

            $post->profile_id = $validatedData['profile_id'];
            $post->save();
            $id = $post->id;

            if ($request->hasFile('post_data')) {
                foreach ($request->file('post_data') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(base_path() . '/public/postImage/', $imageName);
                    $postData['post_data'] = 'postImage/' . $imageName;

                    $postDataCreate = new PostData();
                    $postDataCreate->post_id = $id;
                    $postDataCreate->post_data = $postData['post_data'];
                    $postDataCreate->save();
                }
            }

            $findPost = Post::find($id);
            $findPost = $this->processObject($findPost);
            $findPostimage = $findPost->postRelatedData ?? null;
            foreach ($findPost->postRelatedData as $postRelatedData) {
                $postRelatedData = $this->processObject($postRelatedData);
            }

            $findPostimage = $findPost->postRelatedData ?? null;
            if ($findPostimage->isNotEmpty()) {
                $firstPostRelatedData = $findPostimage->first();
                $firstPostRelatedData = $this->processObject($firstPostRelatedData);
            } else {
                $firstPostRelatedData = null;
            }

            unset($findPost->postRelatedData);

            $findPost->postRelatedData = $firstPostRelatedData;

            unset($findPost->caption);
            unset($findPost->address);
            unset($findPost->name_of_interest);
            unset($findPost->interest_id);
            unset($findPost->status);

            //--------notify with post start--------

            $checkProfile = Profile::select('user_type_id')->where('id', $validatedData['profile_id'])->first();

            if ($checkProfile['user_type_id'] != '3') {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community Does Not Exist',
                ], 404);
            }
            $communityDetail = CommunityDetail::select('name_of_community')->where("profile_id", $validatedData['profile_id'])->first();
            $notificationController = new NotificationController();
            $communityName = str_replace(' ', '', $communityDetail['name_of_community']);
            $baseUrl = URL::to('/');
            $posturl = $baseUrl . '/' . $postDataCreate->post_data;

            $contectType = ($validatedData['post_type'] == '1') ? "IMAGE" : "VIDEO";
            $notification = [
                "title" => $validatedData['title'],
                "body" => $validatedData['body'],
            ];
            $data = [
                "url" => $posturl,
                "message_body" => $validatedData['message_body'],
                "contentType" => $contectType,
                "source" => "Ishtdev",
                "notication" => "true",
            ];
             $response = $notificationController->sendNotificationToAll($communityName, $notification, $data);
            // echo $response;die();
            //--------notify with post end---------
            $findPost->notification = $notification;
            $findPost->message_body = $data['message_body'];
            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Post Uploaded Successfully',
                'data' => $findPost,
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_id' => ['required', 'numeric'],
            'community_id' => ['required', 'numeric'],
            'history_num' => ['required', 'numeric'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 404,
                'status' => 'failure',
                'message' => 'Validation Error',
                'data' => $validator->errors(),
            ], 404);
        }
        $validatedData = $request->only(['profile_id', 'community_id', 'history_num']);
        $userId = Profile::where('id', $validatedData['profile_id'])->select('user_id')->first();
        $device_key = User::select('device_key')->where('id', $userId['user_id'])->first();
        $communityName = CommunityDetail::select('name_of_community')->where('id', $validatedData['community_id'])->first();

        $communityHistory = null;

        if ($validatedData['history_num'] >= 1 && $validatedData['history_num'] <= 5) {
            $communityHistory = CommunityHistory::where('community_detail_id', $validatedData['community_id'])
                ->skip($validatedData['history_num'] - 1)
                ->first();
        }

        if ($validatedData['history_num'] == 5) {
            $checkin = new UsercheckIn();
            $checkin->user_id = $userId['user_id'];
            $checkin->community_id = $validatedData['community_id'];
            $checkin->check_in_count = 1;
            $checkin->save();

            $checkInCount = UserCheckIn::where('user_id', auth()->user()->id)
                ->where('community_id', $request->community_id)
                ->count('check_in_count');

            $getReward = CommunityBadge::where('community_id', $request->community_id)
                ->where('check_in_count', '=', $checkInCount)
                ->where('deleted_at', null)
                ->first();

            if ($getReward) {
                $userCheckIn = UserCheckIn::where('user_id', auth()->user()->id)
                    ->where('community_id', $request->community_id)
                    ->latest()
                    ->first();
                if ($userCheckIn) {
                    $userCheckIn->community_badge_id = $getReward->badge_id;
                    $userCheckIn->save();
                }
            }

            $chechInNotification = new NotificationController();
            $to = $device_key['device_key'];
            $notification = [
                "title" => 'Ishtdev Notification',
                "body" => 'Welcome to ' . $communityName['name_of_community'],
            ];
            $data = [
                "notication" => "true",
                "source" => "CheckIn",
            ];
             $response = $chechInNotification->sendNotificationToOne($to, $notification, $data);
           
        }

        if ($communityHistory) {
            $notificationController = new NotificationController();
            $to = $device_key['device_key'];
            $notification = [
                "title" => 'History of ' . $communityName['name_of_community'],
                "body" => $communityHistory->history,
            ];
            $data = [
                "notication" => "true",
                "source" => "History",
            ];
            $response = $notificationController->sendNotificationToOne($to, $notification, $data);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Notification sent successfully',
                'data' => (object) [],
            ], 200);
        } else {
            return response()->json([
                'code' => 404,
                'status' => 'failure',
                'message' => 'History not found',
                'data' => (object) [],
            ], 404);
        }
    }

}
