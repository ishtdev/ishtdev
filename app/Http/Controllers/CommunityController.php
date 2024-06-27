<?php

/**
 * UserController class file
 *
 * PHP Version 8.1.0
 *
 * @category UserController
 * @package  UserController
 * @author   Ekta Malviya <ekta.malviya@intelliatech.com>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://localhost:8000/api/login
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;
use App\Models\Amenities;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use GuzzleHttp\Client;
use App\Models\PostType;
use App\Models\PostLike;
use App\Models\PostData;
use App\Models\Follows;
use App\Models\PostHashtag;
use App\Models\HashtagMaster;
use App\Models\Profile;
use App\Models\UserCheckIn;
use App\Models\Badge;
use App\Models\CommunityBadge;
use App\Models\CommunityDetail;
use App\Models\CommunityHistory;
use App\Models\CommunityArti;
use App\Models\UserDetails;
use App\Models\CommunityFacility;
//use App\Image;
use Image;
use App\Models\Notification;
use Illuminate\Support\Str;
use JWTAuth, Validator, Hash, URL, Helper, File, Stripe, Session, Exception;
use Carbon\Carbon;
use CommunityArti as GlobalCommunityArti;
use CommunityDetail as GlobalCommunityDetail;
use Illuminate\Support\Arr;


class CommunityController extends Controller
{

    /**
     * PS-4 Level: Process object to remove specified fields.
     *
     * @param mixed $object - The object or array to be processed
     * @return mixed - The processed object or array with specified fields removed
     * 
     */
    private function processObject($object)
    {
        $fieldsToRemove = ['password', 'created_at', 'created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];

        if (!empty($object)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }
        }
        return $object;
    }

    /**
     * PS-4 Level: Add or Update Community Details.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/addupdateCommunity",
     *     summary="Add Update Community",
     *     tags={"Community"},
     *     description="Add or update community details.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Community data",
     *         @OA\JsonContent(
     *             required={"profile_id"},
     *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user creating or updating the community"),
     *             @OA\Property(property="status", type="string", nullable=true, description="Status of the community (approved, pending, etc.)"),
     *             @OA\Property(property="live_arti_link", type="string", format="url", nullable=true, description="Link to the live arti of the community"),
     *             @OA\Property(property="name_of_community", type="string", nullable=true, description="Name of the community"),
     *             @OA\Property(property="short_description", type="string", nullable=true, description="Short description of the community"),
     *             @OA\Property(property="long_description", type="string", nullable=true, description="Long description of the community"),
     *             @OA\Property(property="main_festival_community", type="string", nullable=true, description="Main festival celebrated in the community"),
     *             @OA\Property(property="community_image_background", type="string", format="binary", nullable=true, description="Background image of the community"),
     *             @OA\Property(property="community_image", type="string", format="binary", nullable=true, description="Image representing the community"),
     *             @OA\Property(property="upload_qr", type="string", format="binary", nullable=true, description="Uploaded QR code for the community"),
     *             @OA\Property(property="upload_pdf", type="string", format="binary", nullable=true, description="Uploaded PDF document related to the community"),
     *             @OA\Property(property="upload_video", type="string", format="binary", nullable=true, description="Uploaded video related to the community"),
     *             @OA\Property(property="upload_licence01", type="string", format="binary", nullable=true, description="Uploaded license document 01"),
     *             @OA\Property(property="upload_licence02", type="string", format="binary", nullable=true, description="Uploaded license document 02"),
     *             @OA\Property(property="community_lord_name", type="string", nullable=true, description="Name of the lord or deity worshiped in the community"),
     *             @OA\Property(property="schedual_visit", type="string", nullable=true, description="Schedule for community visits"),
     *             @OA\Property(property="location_of_community", type="string", nullable=true, description="Location of the community"),
     *             @OA\Property(property="distance_from_main_city", type="string", nullable=true, description="Distance of the community from the main city"),
     *             @OA\Property(property="distance_from_airpot", type="string", nullable=true, description="Distance of the community from the airport"),
     *             @OA\Property(property="make_community_private", type="string", example="No", description="Whether the community is private or public"),
     *             @OA\Property(property="community_id", type="integer", format="int32", nullable=true, description="ID of the community to be updated (if updating an existing community)"),
     *           @OA\Property(property="facilities", type="object", nullable=true, description="Object containing facility details"),
     *             @OA\Property(property="facilities.id", type="integer", format="int32", nullable=true, description="ID of the facility to be updated (if updating an existing facility)"),
     *             @OA\Property(property="facilities.key", type="string", nullable=true, description="Key of the facility"),
     *             @OA\Property(property="facilities.value", type="string", nullable=true, description="Value of the facility"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community details added or updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community details added or updated successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile or Community not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Profile or Community not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function addupdateCommunity(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
                'status' => 'nullable',
                'live_arti_link' => 'nullable|url',
                'name_of_community' => 'nullable',
                'website_link' => 'nullable|url',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }
            $data = $request->all();

            if (isset($data['community_id'])) {
                $id = $data['community_id'];
                $id = intval($id);
                $existingRecord = CommunityDetail::find($id);

                if ($existingRecord) {
                    $UserProfile = Profile::where('id', $request->profile_id)
                        ->first();
                    if (!$UserProfile) {
                        return response()->json([
                            'code' => 404,
                            'status' => 'error',
                            'message' => 'Profile not found',
                            'data' => [],
                        ], 404);
                    }
                    $UserDetails = CommunityDetail::where('profile_id', $request->profile_id)->first();
                    if (!$UserDetails) {
                        return response()->json([
                            'code' => 404,
                            'status' => 'error',
                            'message' => 'Community not found',
                            'data' => [],
                        ], 404);
                    }
                    //$user_id = auth()->user()->profile->id;
                    $userDetailsData['profile_id'] = isset($data['profile_id']) ? $data['profile_id'] : $existingRecord['profile_id'];
                    $userDetailsData['created_profile_id'] = $existingRecord['created_profile_id'];
                    $userDetailsData['status'] = isset($data['status']) ? $data['status'] : $existingRecord['status'];
                    $userDetailsData['city'] = isset($data['city']) ? $data['city'] : $existingRecord['city'];
                    $userDetailsData['short_description'] = isset($data['short_description']) ? $data['short_description'] : $existingRecord['short_description'];
                    $userDetailsData['long_description'] = isset($data['long_description']) ? $data['long_description'] : $existingRecord['long_description'];
                    $userDetailsData['main_festival_community'] = isset($data['main_festival_community']) ? $data['main_festival_community'] : $existingRecord['main_festival_community'];

                    $userDetailsData['latitude'] = isset($data['latitude']) ? $data['latitude'] : $existingRecord['latitude'];
                    $userDetailsData['longitude'] = isset($data['longitude']) ? $data['longitude'] : $existingRecord['longitude'];
                    if ($request->location_address) {
                        $userDetailsData['location_address'] = isset($data['location_address']) ? $data['location_address'] : $existingRecord['location_address'];
                    }

                    if ($request->status == 'rejected' || $request->status == 'block') {
                        $userDetailsData['rejection_reason'] = $data['rejection_reason'];
                    } elseif (!$request->has('status')) {
                        if ($existingRecord['status'] == 'rejected' || $existingRecord['status'] == 'block') {
                            $userDetailsData['rejection_reason'] = $existingRecord['rejection_reason'];
                        } else {
                            $userDetailsData['rejection_reason'] = '';
                        }
                    } elseif ($request->status == 'approved' || $request->status == 'approved_with_tick' || $request->status == 'pending') {
                        $userDetailsData['rejection_reason'] = '';
                    }

                    if ($request->name_of_community) {
                        if ($existingRecord['name_of_community'] == $request->name_of_community) {
                            $userDetailsData['name_of_community'] = ucfirst($existingRecord['name_of_community']);
                        } else {
                            $uniqueCommunity = CommunityDetail::where('name_of_community', $request->name_of_community)->first();
                            if ($uniqueCommunity) {
                                return response()->json([
                                    'code' => 404,
                                    'status' => 'error',
                                    'message' => 'Community name alredy exist',
                                    'data' => [],
                                ], 404);
                            }
                            $userDetailsData['name_of_community'] = ucfirst($data['name_of_community']);
                        }
                    } else {
                        $userDetailsData['name_of_community'] = $existingRecord['name_of_community'];
                    }



                    if ($request->hasFile('community_image_background')) {
                        $file = $request->file('community_image_background');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['community_image_background'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('community_image')) {
                        $file = $request->file('community_image');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['community_image'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('upload_qr')) {
                        $file = $request->file('upload_qr');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['upload_qr'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('upload_pdf')) {
                        $file = $request->file('upload_pdf');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['upload_pdf'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('upload_video')) {
                        $file = $request->file('upload_video');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['upload_video'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('upload_licence01')) {
                        $file = $request->file('upload_licence01');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['upload_licence01'] = 'communitydocument/' . $filename;
                    }
                    if ($request->hasFile('upload_licence02')) {
                        $file = $request->file('upload_licence02');
                        $filename = $file->getClientOriginalName();
                        $file->move(base_path() . '/public/communitydocument/', $filename);
                        $userDetailsData['upload_licence02'] = 'communitydocument/' . $filename;
                    }
                    $userDetailsData['community_lord_name'] = isset($data['community_lord_name']) ? $data['community_lord_name'] : $existingRecord['community_lord_name'];
                    $userDetailsData['schedual_visit'] = isset($data['schedual_visit']) ? $data['schedual_visit'] : $existingRecord['schedual_visit'];
                    $userDetailsData['location_of_community'] = isset($data['location_of_community']) ? $data['location_of_community'] : $existingRecord['location_of_community'];
                    $userDetailsData['distance_from_main_city'] = isset($data['distance_from_main_city']) ? $data['distance_from_main_city'] : $existingRecord['distance_from_main_city'];



                    $userDetailsData['distance_from_airpot'] = isset($data['distance_from_airpot']) ? $data['distance_from_airpot'] : $existingRecord['distance_from_airpot'];
                    $userDetailsData['make_community_private'] = isset($data['make_community_private']) ? $data['make_community_private'] : $existingRecord['make_community_private'];
                    $userDetailsData['website_link'] = isset($data['website_link']) ? $data['website_link'] : $existingRecord['website_link'];
                    $existingRecord->update($userDetailsData);
                    $communityId = $data['community_id'];
                    unset($userDetailsData['created_profile_id']);
                    $userDetailsData = ['community_id' => $communityId] + $userDetailsData;
                    $jsonData = json_encode($userDetailsData);

                    $existingArti = CommunityArti::where('community_detail_id', $data['community_id'])->first();
                    if ($existingArti) {
                        $existingArti->live_arti_link = isset($data['live_arti_link']) ? $data['live_arti_link'] : $existingArti->live_arti_link;
                        $existingArti->save();
                    } else {
                        $clientArti = new CommunityArti();
                        $clientArti->live_arti_link = $userDetailsData['live_arti_link'] = isset($data['live_arti_link']) ? $data['live_arti_link'] : "";
                        $clientArti->community_detail_id = $userDetailsData['community_id'] = isset($communityId) ? $communityId : "";
                        $getlastRecordId = $clientArti->save();
                    }




                    //-----------facility update----------
                    $facilityData = isset($data['facilities']) ? json_decode($data['facilities'], true) : [];
                    $community_profile_id = $request->profile_id;
                    if (!empty($facilityData)) {
                        if (isset($facilityData['id'])) {
                            $facility_id = $facilityData['id'];
                            if ($facility_id) {
                                $key = $facilityData['key'];
                                $value = $facilityData['value'];
                                $city = $facilityData['city'];
                                $facility = CommunityFacility::where('id', $facility_id)
                                    ->where('community_profile_id', $community_profile_id)
                                    ->first();

                                if ($facility) {
                                    $facility->key = $key;
                                    $facility->value = $value;
                                    $facility->city = $city;
                                    $facility->save();
                                } else {
                                    return response()->json([
                                        'code' => 404,
                                        'status' => 'error',
                                        'message' => 'Facility does not exist',
                                        'data' => [],
                                    ], 404);
                                }
                            }
                        } else {
                            $createdRecordIds = [];
                            $community_profile_id = $request->profile_id;

                            foreach ($facilityData as $facilityType => $facility) {
                                foreach ($facility as $facilityName => $facilityValues) {
                                    $keys = [];
                                    $values = [];
                                    $cities = [];
                                    foreach ($facilityValues as $facilityValue) {
                                        $keys[] = $facilityValue['key'];
                                        $values[] = $facilityValue['value'];
                                        $cities[] = $facilityValue['city'] ?? '';
                                    }
                                    foreach ($keys as $index => $keyToAdd) {
                                        $newFacility = new CommunityFacility([
                                            'community_profile_id' => $community_profile_id,
                                            'facility' => $facilityName,
                                            'key' => $keyToAdd,
                                            'value' => $values[$index] ?? '',
                                            'city' => $cities[$index] ?? '',
                                        ]);
                                        $newFacility->save();
                                        $createdRecordIds[] = $newFacility->id;
                                    }
                                }
                            }
                        }
                    }
                    //-----------facility update end----------
                    //-------edit badge start--------
                    $badgesData = isset($request['badge']) ? json_decode($request['badge'], true) : [];
                    if ($request->has('badges')) {

                        foreach ($badgesData['badges'] as $badgeData) {
                            if (!isset($badgeData['badge_id'])) {
                                return response()->json([
                                    'code' => 400,
                                    'status' => 'failure',
                                    'message' => 'Invalid badge data.',
                                    'data' => (object) [],
                                ]);
                            }
                            if (isset($badgeData['id'])) {
                                $badgeExisted = CommunityBadge::where('id', $badgeData['id'])->where('community_id', $communityId)
                                    ->first();
                                if ($badgeExisted) {
                                    $badgeExisted->title = $badgeData['title'] ?? $badgeExisted->title;
                                    $badgeExisted->badge_id = $badgeData['badge_id'] ?? $badgeExisted->badge_id;
                                    $badgeExisted->check_in_count = $badgeData['check_in_count'] ?? $badgeExisted->check_in_count;
                                    $badgeExisted->save();
                                } else {
                                    $newBadge = new CommunityBadge();
                                    $newBadge->title = $badgeData['title'] ?? '';
                                    $newBadge->community_id = $communityId;
                                    $newBadge->badge_id = $badgeData['badge_id'];
                                    $newBadge->check_in_count = $badgeData['check_in_count'] ?? 0;
                                    $newBadge->save();
                                }
                            } else {
                                $newBadge = new CommunityBadge();
                                $newBadge->title = $badgeData['title'] ?? '';
                                $newBadge->community_id = $communityId;
                                $newBadge->badge_id = $badgeData['badge_id'];
                                $newBadge->check_in_count = $badgeData['check_in_count'] ?? 0;
                                $newBadge->save();
                            }

                            $userIdsWithMatchingCheckInCount = UserCheckIn::select('user_id')
                                ->selectRaw('COUNT(*) as check_in_count')
                                ->where('community_id', $communityId)
                                ->groupBy('user_id')
                                ->having('check_in_count', '>=', $badgeData['check_in_count'])
                                ->pluck('user_id');

                            foreach ($userIdsWithMatchingCheckInCount as $userId) {
                                $rewardBadge = CommunityBadge::where('community_id', $communityId)
                                    ->where('check_in_count', '=', $badgeData['check_in_count'])
                                    ->whereNull('deleted_at')
                                    ->first();

                                if ($rewardBadge) {
                                    $latestCheckIn = UserCheckIn::where('user_id', $userId)
                                        ->where('community_id', $communityId)
                                        ->latest()
                                        ->first();

                                    if ($latestCheckIn) {
                                        $latestCheckIn->community_badge_id = $rewardBadge->id;
                                        $latestCheckIn->save();
                                    }
                                }
                            }
                        }

                    }
                    //-------edit badge ends---------

                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Community Detail update successfully',
                        'data' => array("community_detail" => $userDetailsData),
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Community not found',
                        'data' => [],
                    ], 404);
                }
            } else {
                $UserProfile = Profile::where('user_id', auth()->user()->id)->where('id', $request->profile_id)
                    ->first();
                if (!$UserProfile) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Profile not found',
                        'data' => [],
                    ], 404);
                }
                $UserDetails = UserDetails::where('profile_id', $request->profile_id)
                    ->where('become_pandit', "approved")
                    ->first();
                if (!$UserDetails) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Please become pandit first',
                        'data' => [],
                    ], 404);
                }
                $uniqueCommunity = CommunityDetail::where('name_of_community', $request->name_of_community)->first();
                if ($uniqueCommunity) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Community name alredy exist',
                        'data' => [],
                    ], 404);
                }
                $created_profile_id = auth()->user()->profile->id;
                $user_id = auth()->user()->id;
                $profile = new Profile();
                $profile->user_id = $user_id;
                $profile->user_type_id = "3";
                $lastprofileid = $profile->save();

                $client = new CommunityDetail();
                $clientArti = new CommunityArti();
                $client->created_profile_id = $created_profile_id;
                $client->profile_id = $profile->id;
                $autoApprove = User::select('mobile_number')->where('id', auth()->user()->id)->first();
                if ($autoApprove['mobile_number'] == '8085355117') {
                    $client->status = $userDetailsData['status'] = "approved";
                } else {
                    $client->status = $userDetailsData['status'] = isset($data['status']) ? $data['status'] : "pending";
                }
                //$client->status = $userDetailsData['status'] = isset($data['status']) ? $data['status'] :  "approved";
                $client->name_of_community = $userDetailsData['name_of_community'] = isset($data['name_of_community']) ? ucfirst($data['name_of_community']) : "";
                $client->city = $userDetailsData['city'] = isset($data['city']) ? ucfirst($data['city']) : "";
                $client->short_description = $userDetailsData['short_description'] = isset($data['short_description']) ? $data['short_description'] : "";
                $client->long_description = $userDetailsData['long_description'] = isset($data['long_description']) ? $data['long_description'] : "";
                $client->main_festival_community = $userDetailsData['main_festival_community'] = isset($data['main_festival_community']) ? $data['main_festival_community'] : "";
                $client->website_link = $userDetailsData['website_link'] = isset($data['website_link']) ? $data['website_link'] : "";

                $client->latitude = $userDetailsData['latitude'] = isset($data['latitude']) ? $data['latitude'] : "";
                $client->longitude = $userDetailsData['longitude'] = isset($data['longitude']) ? $data['longitude'] : "";
                if ($request->location_address) {
                    $client->location_address = $userDetailsData['location_address'] = isset($data['location_address']) ? $data['location_address'] : "";
                }

                if ($request->hasFile('community_image_background')) {
                    $file = $request->file('community_image_background');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->community_image_background = $userDetailsData['community_image_background'] = 'communitydocument/' . $filename;
                }
                if ($request->hasFile('community_image')) {
                    $file = $request->file('community_image');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->community_image = $userDetailsData['community_image'] = 'communitydocument/' . $filename;
                }
                if ($request->hasFile('upload_qr')) {
                    $file = $request->file('upload_qr');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->upload_qr = $userDetailsData['upload_qr'] = 'communitydocument/' . $filename;

                }
                if ($request->hasFile('upload_pdf')) {
                    $file = $request->file('upload_pdf');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->upload_pdf = $userDetailsData['upload_pdf'] = 'communitydocument/' . $filename;
                }
                if ($request->hasFile('upload_video')) {
                    $file = $request->file('upload_video');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->upload_video = $userDetailsData['upload_video'] = 'communitydocument/' . $filename;
                }
                if ($request->hasFile('upload_licence01')) {
                    $file = $request->file('upload_licence01');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->upload_licence01 = $userDetailsData['upload_licence01'] = 'communitydocument/' . $filename;
                }
                if ($request->hasFile('upload_licence02')) {
                    $file = $request->file('upload_licence02');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/communitydocument/', $filename);
                    $client->upload_licence02 = $userDetailsData['upload_licence02'] = 'communitydocument/' . $filename;
                }
                $client->community_lord_name = $userDetailsData['community_lord_name'] = isset($data['community_lord_name']) ? $data['community_lord_name'] : "";
                $client->schedual_visit = $userDetailsData['schedual_visit'] = isset($data['schedual_visit']) ? $data['schedual_visit'] : "";
                $client->location_of_community = $userDetailsData['location_of_community'] = isset($data['location_of_community']) ? $data['location_of_community'] : "";
                $client->distance_from_main_city = $userDetailsData['distance_from_main_city'] = isset($data['distance_from_main_city']) ? $data['distance_from_main_city'] : "";
                $client->distance_from_airpot = $userDetailsData['distance_from_airpot'] = isset($data['distance_from_airpot']) ? $data['distance_from_airpot'] : "";
                $client->make_community_private = $userDetailsData['make_community_private'] = isset($data['make_community_private']) ? $data['make_community_private'] : "No";

                $getlastRecordId = $client->save();
                $communityId = $client->id;
                $profile_id = $client->profile_id;

                $clientArti->live_arti_link = $userDetailsData['live_arti_link'] = isset($data['live_arti_link']) ? $data['live_arti_link'] : "";
                $clientArti->community_detail_id = $userDetailsData['community_id'] = isset($communityId) ? $communityId : "";
                $getlastRecordId = $clientArti->save();

                //-------facility add----------

                $facilitiesData = isset($data['facilities']) ? json_decode($data['facilities'], true) : [];
                $createdRecordIds = [];
                $community_profile_id = $client->profile_id;

                foreach ($facilitiesData as $facilityType => $facility) {
                    foreach ($facility as $facilityName => $facilityValues) {
                        $keys = [];
                        $values = [];
                        $cities = [];
                        foreach ($facilityValues as $facilityValue) {
                            $keys[] = $facilityValue['key'];
                            $values[] = $facilityValue['value'];
                            $cities[] = $facilityValue['city'] ?? '';
                        }
                        $facilityExistingRecords = CommunityFacility::where('community_profile_id', $community_profile_id)
                            ->where('facility', $facilityName)
                            ->get();

                        $existingKeys = $facilityExistingRecords->pluck('key')->toArray();
                        $keysToAdd = array_diff($keys, $existingKeys);
                        foreach ($keysToAdd as $index => $keyToAdd) {

                            // echo"<pre>";print_r($facilityName);echo " "; print_r($keyToAdd);echo " "; print_r($values[$index]);echo " ";print_r( $cities[$index]);echo"\n";

                            $newFacility = new CommunityFacility([
                                'community_profile_id' => $community_profile_id,
                                'facility' => $facilityName,
                                'key' => $keyToAdd,
                                'value' => $values[$index] ?? '',
                                'city' => $cities[$index] ?? '',
                            ]);

                            $newFacility->save();
                            $createdRecordIds[] = $newFacility->id;
                        }
                    }
                }

                // die;
                //-------facility add ends------
                //-------add badge start--------
                $badgesData = isset($request['badge']) ? json_decode($request['badge'], true) : [];

                foreach ($badgesData['badges'] as $badgeData) {
                    if (!isset($badgeData['badge_id'])) {
                        return response()->json([
                            'code' => 400,
                            'status' => 'failure',
                            'message' => 'Invalid badge data. Missing badge_id.',
                            'data' => (object) [],
                        ]);
                    }

                    $badge = Badge::where('id', $badgeData['badge_id'])->first();
                    if (!$badge) {
                        return response()->json([
                            'code' => 404,
                            'status' => 'failure',
                            'message' => 'Badge does not exist',
                            'data' => (object) [],
                        ]);
                    }

                    $badgeExisted = CommunityBadge::where('community_id', $communityId)
                        ->where('badge_id', $badgeData['badge_id'])
                        ->first();

                    if (!$badgeExisted) {
                        $newBadge = new CommunityBadge();
                        $newBadge->title = $badgeData['title'] ?? '';
                        $newBadge->community_id = $communityId;
                        $newBadge->badge_id = $badgeData['badge_id'];
                        $newBadge->check_in_count = $badgeData['check_in_count'] ?? 0;
                        $newBadge->save();
                    }

                    $userIdsWithMatchingCheckInCount = UserCheckIn::select('user_id')
                        ->selectRaw('COUNT(*) as check_in_count')
                        ->where('community_id', $communityId)
                        ->groupBy('user_id')
                        ->having('check_in_count', '>=', $badgeData['check_in_count'])
                        ->pluck('user_id');

                    foreach ($userIdsWithMatchingCheckInCount as $userId) {
                        $rewardBadge = CommunityBadge::where('community_id', $communityId)
                            ->where('check_in_count', '=', $badgeData['check_in_count'])
                            ->whereNull('deleted_at')
                            ->first();

                        if ($rewardBadge) {
                            $latestCheckIn = UserCheckIn::where('user_id', $userId)
                                ->where('community_id', $communityId)
                                ->latest()
                                ->first();

                            if ($latestCheckIn) {
                                $latestCheckIn->community_badge_id = $rewardBadge->id;
                                $latestCheckIn->save();
                            }
                        }
                    }
                }


                //-------add badge ends---------
                $userDetailsData = ['profile_id' => $profile_id] + $userDetailsData;
                $userDetailsData = ['community_id' => $communityId] + $userDetailsData;

                //-------create channel start----------
                $notificationController = new NotificationController();
                $communityName = str_replace(' ', '', $userDetailsData['name_of_community']);

                $deviceKey = User::select('device_key')->where('id', auth()->user()->id)->first();
                $registrationTokens = [$deviceKey['device_key']];

                $response = $notificationController->createChannel($communityName, $registrationTokens);
                //echo $response;die();

                //-------create channel end----------
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community created successfully',
                    'data' => array('CommunityDetails' => $userDetailsData),
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

    /**
     * PS-4 Level: Get User Communities.
     *
     * @param int $userid The user ID.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/api/getUserCommunity/{userid}",
     *     summary="Get User Community",
     *     tags={"Community"},
     *     description="Get communities associated with a user.",
     *     @OA\Parameter(
     *         name="userid",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All communities retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the community"),
     *                 @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user"),
     *                 @OA\Property(property="name_of_community", type="string", example="Community Name", description="Name of the community"),
     *                 @OA\Property(property="community_image", type="string", example="community_image.jpg", description="Image representing the community"),
     *                 @OA\Property(property="community_image_background", type="string", example="community_image_background.jpg", description="Background image of the community"),
     *                 @OA\Property(property="short_description", type="string", example="Short description of the community", description="Short description of the community"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User, Profile, or Community not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User, Profile, or Community not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function getUserCommunity($profileid)
    { //to show all community of a user 
        try {
            $User = Profile::where('id', $profileid)
                ->first();
            if (!$User) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                    'data' => [],
                ], 404);
            }

            $UserDetails = Profile::where('id', $profileid)
                ->where('user_type_id', "2")
                ->first();
            // $UserId = Profile::select('user_id')->where('id', $profileid)
            // ->where('user_type_id', "2")
            // ->first();

            if (!$UserDetails) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Please become pandit first',
                    'data' => [],
                ], 404);
            }
            $communities = CommunityDetail::where('created_profile_id', $profileid)->get();
            if ($communities->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No community found',
                    'data' => [],
                ], 404);
            }
            $filteredCommunities = [];

            foreach ($communities as $community) {
                $rejection_reason = ($community->status == 'approved' || $community->status == 'approved_with_tick' || $community->status == 'pending') ? '' : $community->rejection_reason;

                $filteredCommunities[] = [
                    'id' => $community->id,
                    'profile_id' => $community->profile_id,
                    'name_of_community' => ucfirst($community->name_of_community),
                    'status' => $community->status,
                    'rejection_reason' => $rejection_reason,
                    'community_image' => $community->community_image,
                    'community_image_background' => $community->community_image_background,
                    'short_description' => $community->short_description,
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All communities retrieved successfully',
                'data' => $filteredCommunities,
            ], 200);

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

    /**
     * PS-4 Level: Get User Followed Communities.
     *
     * @param int $userid The user ID.
     *
     * @return \Illuminate\Http\JsonResponse
     *  @OA\Post(
     *     path="/api/showFollowedCommunities",
     *     summary="Show Followed Communities",
     *     tags={"Community"},
     *     description="Show communities followed by a user.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Community data",
     *         @OA\JsonContent(
     *             required={"profile_id"},
     *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user"),
     *             @OA\Property(property="status", type="string", nullable=true, description="Status of the community (approved, pending, etc.)"),
     *             @OA\Property(property="live_arti_link", type="string", format="url", nullable=true, description="Link to the live arti of the community"),
     *             @OA\Property(property="name_of_community", type="string", nullable=true, description="Name of the community"),
     *             @OA\Property(property="short_description", type="string", nullable=true, description="Short description of the community"),
     *             @OA\Property(property="long_description", type="string", nullable=true, description="Long description of the community"),
     *             @OA\Property(property="community_id", type="integer", format="int32", nullable=true, description="ID of the community to be updated (if updating an existing community)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All communities retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the community"),
     *                 @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user"),
     *                 @OA\Property(property="name_of_community", type="string", example="Community Name", description="Name of the community"),
     *                 @OA\Property(property="community_image", type="string", example="community_image.jpg", description="Image representing the community"),
     *                 @OA\Property(property="short_description", type="string", example="Short description of the community", description="Short description of the community"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile or Community not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Profile or Community not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function showFollowedCommunities(Request $request)
    { //to show all followed community of a user 
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 404);
            }
            ;
            $UserDetails = Profile::where('id', $request->profile_id)->first();
            if (!$UserDetails) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                    'data' => [],
                ], 404);
            }
            $followedProfile = Follows::where('following_profile_id', $request->profile_id)->get();
            $community = [];
            foreach ($followedProfile as $userDetail) {
                $followedProfileId = $userDetail->followed_profile_id;
                $followcommunities = Profile::where('id', $followedProfileId)->where('user_type_id', 3)->first();
                $communities = CommunityDetail::where('profile_id', $followedProfileId)->get();
                $community = array_merge($community, $communities->toArray());
            }
            $filteredCommunity = array_map(function ($communityItem) {
                return [
                    'id' => $communityItem['id'],
                    'profile_id' => $communityItem['profile_id'],
                    'name_of_community' => $communityItem['name_of_community'],
                    'status' => $communityItem['status'],
                    'community_image' => $communityItem['community_image'],
                    'short_description' => $communityItem['short_description'],
                ];
            }, $community);
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All communities retrieved successfully',
                'data' => $filteredCommunity,
            ], 200);
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

    /**
     * @OA\Get(
     *     path="/api/showAllCommunities",
     *     summary="Get all communities",
     *     tags={"Community"},
     *     description="Retrieve information about all communities.",
     *     @OA\Response(
     *         response=200,
     *         description="Communities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All communities retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the community"),
     *                 @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the community owner"),
     *                 @OA\Property(property="name_of_community", type="string", example="Community Name", description="Name of the community"),
     *                 @OA\Property(property="community_image", type="string", example="community_image.jpg", description="Image representing the community"),
     *                 @OA\Property(property="community_image_background", type="string", example="community_bg.jpg", description="Background image of the community"),
     *                 @OA\Property(property="short_description", type="string", example="Short description of the community", description="Short description of the community"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No community found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="No community found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function showAllCommunities(Request $request)
    {
        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            if ($request->latitude && $request->longitude) {
                $communities = CommunityDetail::select(
                    'id',
                    'profile_id',
                    'status',
                    'name_of_community',
                    'community_image',
                    'community_image_background',
                    'latitude',
                    'longitude',
                    'short_description',
                    'location_of_community',
                    'created_at',
                    'city',
                    \DB::raw("(6373 * acos(cos(radians($latitude))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians($longitude))
                + sin(radians($latitude)) * sin(radians(latitude)))) AS distance_in_km")
                )
                    ->whereRaw("status IN ('approved', 'approved_with_tick')")
                    ->whereNotNull('latitude')
                    ->orderBy('distance_in_km', 'ASC')
                    ->paginate(10);

                if ($communities->isEmpty()) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'failure',
                        'message' => 'No approved communities found',
                        'data' => [],
                    ], 404);
                }

                $filteredCommunities = $communities->map(function ($community) {
                    return [
                        'id' => $community->id,
                        'profile_id' => $community->profile_id,
                        'name_of_community' => ucfirst($community->name_of_community),
                        'status' => $community->status,
                        'community_image' => $community->community_image,
                        'community_image_background' => $community->community_image_background,
                        'short_description' => $community->short_description,
                        'latitude' => $community->latitude,
                        'longitude' => $community->longitude,
                        'distance_in_km' => $community->distance_in_km,
                        'address' => $community->location_of_community,
                        'created_at' => $community->created_at,
                        'city' => $community->city,
                    ];
                });
            } else {
                $communities = CommunityDetail::select(
                    'id',
                    'profile_id',
                    'status',
                    'name_of_community',
                    'community_image',
                    'community_image_background',
                    'latitude',
                    'longitude',
                    'short_description',
                    'location_of_community',
                    'created_at',
                    'city',
                )
                    ->whereRaw("status IN ('approved', 'approved_with_tick')")
                    ->paginate(10);

                if ($communities->isEmpty()) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'failure',
                        'message' => 'No approved communities found',
                        'data' => [],
                    ], 404);
                }

                $filteredCommunities = $communities->map(function ($community) {
                    return [
                        'id' => $community->id,
                        'profile_id' => $community->profile_id,
                        'name_of_community' => ucfirst($community->name_of_community),
                        'status' => $community->status,
                        'community_image' => $community->community_image,
                        'community_image_background' => $community->community_image_background,
                        'short_description' => $community->short_description,
                        'latitude' => $community->latitude,
                        'longitude' => $community->longitude,
                        'distance_in_km' => null,
                        'address' => $community->location_of_community,
                        'created_at' => $community->created_at,
                        'city' => $community->city,
                    ];
                });
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Approved communities retrieved successfully',
                'data' => $filteredCommunities,
            ], 200);
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

    public function showAllCommunity()
    {
        try {
            $communities = CommunityDetail::get();

            if ($communities->count() === 0) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No community found',
                    'data' => [],
                ], 404);
            }
            $filteredCommunities = [];
            foreach ($communities as $community) {

                $filteredCommunities[] = [
                    'id' => $community->id,
                    'profile_id' => $community->profile_id,
                    'name_of_community' => ucfirst($community->name_of_community),
                    'status' => $community->status,
                    'community_image' => $community->community_image,
                    'community_image_background' => $community->community_image_background,
                    'short_description' => $community->short_description,
                    'created_at' => [
                        'date' => date('Y-m-d', strtotime($community->created_at)), // Date only
                        'time' => date('H:i:s', strtotime($community->created_at)), // Time only
                    ],
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All communities retrieved successfully',
                'data' => $filteredCommunities,
            ], 200);


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

    /**
     * 
     * PS-4 Level: Get User Communities.
     *
     * @param int $userid The user ID.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showCommunityDetails($communityid)
    {
        try {
            $communities = CommunityDetail::where('profile_id', $communityid)->get();

            if ($communities->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No community found',
                    'data' => [],
                ], 404);
            }
            $filteredCommunities = [];

            foreach ($communities as $community) {

                $rejectionReason = ($community->status === "rejected" || $community->status === "block") ? $community->rejection_reason : null;
                $liveArtiUrl = CommunityArti::where('community_detail_id', $community->id)->first()->live_arti_link ?? null;
                $facilities = CommunityFacility::where('community_profile_id', $community->profile_id)->get()->toArray();


                $formattedFacilities = [];
                foreach ($facilities as $facility) {
                    if (!isset($formattedFacilities[$facility['facility']])) {
                        $formattedFacilities[$facility['facility']] = [];
                    }
                    $amenityIcon = '';

                    if ($facility['facility'] === 'amenities' && $facility['key']) {

                        $amenityIcon = Amenities::select('icon')->where('amenity_name', $facility['key'])->where('deleted_at', null)->first();

                        $formattedFacilities[$facility['facility']][] = [
                            'id' => $facility['id'],
                            'community_profile_id' => $facility['community_profile_id'],
                            'facility' => $facility['facility'],
                            'key' => $facility['key'],
                            'value' => $facility['value'],
                            'icon' => $amenityIcon['icon'],
                        ];
                        // echo"<pre>"; print_r($amenityIcon['icon']); 
                    } else {
                        $formattedFacilities[$facility['facility']][] = [
                            'id' => $facility['id'],
                            'community_profile_id' => $facility['community_profile_id'],
                            'facility' => $facility['facility'],
                            'key' => $facility['key'],
                            'value' => $facility['value'],
                        ];
                    }

                }

                $badges = CommunityBadge::where('community_id', $community->id)
                    ->with('badge_type.lord')->get()->toArray();

                $formattedBadges = [];
                foreach ($badges as $badge) {
                    $badgeId = $badge['badge_id'];
                    $formattedBadges['badge'][] = [
                        'id' => $badge['id'],
                        'community_id' => $badge['community_id'],
                        'title' => $badge['title'],
                        'check_in_count' => $badge['check_in_count'],
                        'badge_detail' => [
                            'lord_id' => $badge['badge_type']['lord_id'],
                            'lord_name' => $badge['badge_type']['lord']['lord_name'],
                            'type' => $badge['badge_type']['type'],
                            'image' => $badge['badge_type']['image'],
                        ],
                    ];
                }

                $filteredCommunities[] = [
                    'id' => $community->id,
                    'profile_id' => $community->profile_id,
                    'name_of_community' => $community->name_of_community,
                    'short_description' => $community->short_description,
                    'long_description' => $community->long_description,
                    'main_festival_community' => $community->main_festival_community,
                    'upload_qr' => $community->upload_qr,
                    'upload_pdf' => $community->upload_pdf,
                    'upload_video' => $community->upload_video,
                    'location_of_community' => $community->location_of_community,
                    'distance_from_main_city' => $community->distance_from_main_city,
                    'distance_from_airpot' => $community->distance_from_airpot,
                    'upload_licence01' => $community->upload_licence01,
                    'upload_licence02' => $community->upload_licence02,
                    'schedual_visit' => $community->schedual_visit,
                    'make_community_private' => $community->make_community_private,
                    'community_lord_name' => $community->community_lord_name,
                    'status' => $community->status,
                    'rejection_reason' => $rejectionReason,
                    'community_image' => $community->community_image,
                    'community_image_background' => $community->community_image_background,
                    'latlong' => $community->latlong,
                    'live_arti_url' => $liveArtiUrl,
                    'webiste_link' => $community->website_link,
                    'facility' => empty($formattedFacilities) ? null : $this->processObject($formattedFacilities),
                    'badge' => empty($formattedBadges['badge']) ? null : $this->processObject($formattedBadges['badge']),
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All communities retrieved successfully',
                'data' => $this->processObject($filteredCommunities),
            ], 200);


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


    /**
     * PS-4 Level: Add or Update Community History.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/addCommunityHistory",
     *     summary="Add/Update Community History",
     *     tags={"Community"},
     *     description="Add or update community history.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"history_id", "community_id", "history"},
     *             @OA\Property(property="history", type="string", example="John Doe", description="User's full name"),
     *             @OA\Property(property="history_id", type="integer", format="number", example=1, description="Community history ID (nullable)"),
     *             @OA\Property(property="community_id", type="integer", format="number", example=2, description="Community ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community history added or updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="history", type="string", example="John Doe", description="User's full name"),
     *             @OA\Property(property="history_id", type="integer", format="number", example=1, description="Community history ID"),
     *             @OA\Property(property="community_id", type="integer", format="number", example=2, description="Community ID"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input", description="Error message")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function addCommunityHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'community_id' => 'required',
                'history' => 'required|max:225',
                'history_id' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $data = $request->all();
            $History = CommunityDetail::where('id', $data['community_id'])->first();

            if (!$History) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community not found',
                    'data' => [],
                ], 404);
            }

            if (!isset($data['history_id'])) {
                $History = CommunityHistory::where('community_detail_id', $data['community_id'])->first();

                $communityHistory = new CommunityHistory();
                $communityHistory->community_detail_id = $data['community_id'];
                $communityHistory->history = $data['history'];
                $communityHistory->save();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community history added successfully',
                    'data' => $this->processObject($communityHistory),
                ], 200);
            }

            $communityHistory = CommunityHistory::where('community_detail_id', $request->community_id)->where('id', $data['history_id'])->first();

            if (!$communityHistory) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community History not found',
                    'data' => [],
                ], 404);
            }

            $communityHistoryData['history'] = isset($data['history']) ? $data['history'] : $communityHistory->history;
            $communityHistory->update($communityHistoryData);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community history updated successfully',
                'data' => $communityHistoryData,
            ], 200);

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

    /**
     * PS-4 Level: Show Community History.
     *
     * @param int $communityId The ID of the community.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/api/showCommunityHistory/{communityId}",
     *     summary="Show Community History",
     *     tags={"Community"},
     *     @OA\Parameter(
     *         name="communityId",
     *         in="path",
     *         required=true,
     *         description="ID of the community",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community histories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community histories retrieved successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community or Community History not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community or Community History not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function showCommunityHistory($communityId)
    {     //to show community history
        try {
            $history = CommunityDetail::where('id', $communityId)->first();
            if (!$history) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community not found',
                    'data' => [],
                ], 404);
            }

            $communityHistory = CommunityHistory::where('community_detail_id', $communityId)->where('status', 1)->get();
            if ($communityHistory->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community History not found',
                    'data' => [],
                ], 404);
            }

            $filteredHistories = [];
            foreach ($communityHistory as $history) {
                $filteredHistories[] = [
                    'id' => $history->id,
                    'profile_id' => $history->communityDetail->profile_id,
                    'community_detail_id' => $history->community_detail_id,
                    'history' => $history->history,
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community histories retrieved successfully',
                'data' => $this->processObject($filteredHistories),
            ], 200);

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


    /**
     * PS-4 Level: Add and Update Community Arti Time.
     *
     * @param \Illuminate\Http\Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Post(
     *     path="/api/addupdateCommunityArti",
     *     summary="Add/Update Community Arti",
     *     tags={"Community"},
     *     description="Endpoint to add or update community arti time.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Community arti details",
     *         @OA\JsonContent(
     *             required={"community_id", "arti_time", "live_arti_link", "arti_id"},
     *             @OA\Property(property="community_id", type="integer", format="number", example=1, description="Community ID"),
     *             @OA\Property(property="arti_time", type="string", example="18:00", description="Arti time"),
     *             @OA\Property(property="live_arti_link", type="string", format="url", example="https://example.com/live-arti", description="Link to the live arti (nullable)"),
     *             @OA\Property(property="arti_id", type="integer", format="number", example=2, description="Community arti ID (nullable)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community arti time added or updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community arti time added or updated successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community not found or Community Arti not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community not found or Community Arti not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function addupdateCommunityArti(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'community_id' => 'required',
                'arti_time' => 'required',
                'live_arti_link' => 'nullable|url',
                'arti_id' => 'nullable',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }
            $data = $request->all();
            $openCloseTime = CommunityDetail::where('id', $data['community_id'])->first();
            if (!$openCloseTime) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community not found',
                    'data' => [],
                ], 404);
            }
            if (!isset($data['arti_id'])) {
                $openCloseTime = CommunityArti::where('community_detail_id', $data['community_id'])->first();
                $communityOpenCloseTime = new CommunityArti();
                $communityOpenCloseTime->community_detail_id = $data['community_id'];
                $communityOpenCloseTime->arti_time = $data['arti_time'];
                $communityOpenCloseTime->live_arti_link = $data['live_arti_link'];
                $communityOpenCloseTime->save();
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community arti time added successfully',
                    'data' => $this->processObject($communityOpenCloseTime),
                ], 200);
            }
            $openCloseTime = CommunityArti::where('community_detail_id', $request->community_id)->where('id', $data['arti_id'])->first();
            if (!$openCloseTime) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community arti not found',
                    'data' => [],
                ], 404);
            }
            $communityTimeData['arti_time'] = isset($data['arti_time']) ? $data['arti_time'] : $communityTimeData->arti_time;
            $communityTimeData['live_arti_link'] = isset($data['live_arti_link']) ? $data['live_arti_link'] : $communityTimeData->live_arti_link;
            $openCloseTime->update($communityTimeData);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community arti time updated successfully',
                'data' => $communityTimeData,
            ], 200);

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

    /**
     * PS-4 Level: Show List of Community Arti Time.
     *
     * @param int $communityId The community ID.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/api/showCommunityArti/{communityId}",
     *     summary="Show Community Arti",
     *     tags={"Community"},
     *     description="Retrieve list of arti times for a specific community.",
     *     @OA\Parameter(
     *         name="communityId",
     *         in="path",
     *         required=true,
     *         description="ID of the community",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community arti times retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community arti times retrieved successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community or Arti Time not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community or Arti Time not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function showCommunityArti($communityId)
    {  //to show list of community arti time 
        try {
            $communityExist = CommunityDetail::where('id', $communityId)->first();
            if (!$communityExist) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community not found',
                    'data' => [],
                ], 404);
            }

            $communityArtiTime = CommunityArti::where('community_detail_id', $communityId)->get();
            if ($communityArtiTime->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Arti time not found',
                    'data' => [],
                ], 404);
            }

            $filteredCommunityArti = [];
            foreach ($communityArtiTime as $communityArtiTimeData) {
                $filteredCommunityArti[] = [
                    'id' => $communityArtiTimeData->id,
                    'community_detail_id' => $communityArtiTimeData->community_detail_id,
                    'arti_time' => $communityArtiTimeData->arti_time,
                    'live_arti_link' => $communityArtiTimeData->live_arti_link,
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community arti retrieved successfully',
                'data' => $filteredCommunityArti,
            ], 200);

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


    /**
     * PS-4 Level: check unique commuynity name.
     *
     * @param string $name The name of the community to check availability for.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/uniqueCommunityName",
     *     summary="Check Community Name Availability",
     *     tags={"Community"},
     *     description="Check if a community name is available or already exists.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Name of the community",
     *         @OA\Schema(type="string", example="Community Name"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response indicating whether the community name is available.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community name available"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Community name already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community name already exist"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function uniqueCommunityName(Request $request)
    {
        try {
            $searchValue = $request->name;

            $existingRecord = CommunityDetail::where("name_of_community", $searchValue)->first();
            if (isset($existingRecord)) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community name already exist',
                    'data' => array(
                        "isUserExisting" => true,
                    ),
                ]);
            }
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community name available',
                'data' => array(
                    "isUserExisting" => false,
                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }

    /**
     * Delete Community History.
     *
     * PS-4 Level: This endpoint is used to delete community history based on the provided community ID and history ID.
     * It updates the status and deleted_at fields of the community history record.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing community ID, history ID, status, and optional deleted_at.
     * @return \Illuminate\Http\JsonResponse JSON response indicating the success or failure of the operation.
     * @OA\Post(
     *     path="/api/deleteCommunityHistory",
     *     summary="Delete Community History",
     *     tags={"Community"},
     *     description="Delete community history based on community ID and history ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Provide community ID, history ID, status, and optional deleted_at.",
     *         @OA\JsonContent(
     *             required={"community_id", "status", "history_id"},
     *             @OA\Property(property="community_id", type="integer", example=1, description="The ID of the community."),
     *             @OA\Property(property="history_id", type="integer", example=1, description="The ID of the history record to delete."),
     *             @OA\Property(property="status", type="string", example="0", description="The updated status of the history record."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response indicating the community history was deleted successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community history deleted successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed. Invalid request parameters.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community or Community History not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community or Community History not found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function deleteCommunityHistory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'community_id' => 'required',
                'status' => 'required',
                'history_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $data = $request->all();
            $History = CommunityDetail::where('id', $data['community_id'])->first();
            if (!$History) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community not found',
                    'data' => (object) [],
                ], 404);
            }
            $communityHistory = CommunityHistory::where('community_detail_id', $data['community_id'])->where('id', $data['history_id'])
                ->first();

            if (!$communityHistory) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community History not found',
                    'data' => (object) [],
                ], 404);
            } else {
                $communityHistoryData['status'] = isset($data['status']) ? $data['status'] : $communityHistory->status;
                $communityHistoryData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                $communityHistory->update($communityHistoryData);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community history deleted successfully',
                    'data' => $communityHistoryData,
                ], 200);

            }


        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => (object) [],
            ], 500);
        }
    }

    /**
     * Delete a facility by ID.
     *
     * @param int $facility_id The ID of the post to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the status of the delete operation.
     * 
     * @OA\Delete(
     *     path="/api/deleteCommunityFacility/{facility_id}",
     *     summary="Delete facility",
     *     tags={"Community"},
     *     @OA\Parameter(
     *         name="facility_id",
     *         in="path",
     *         required=true,
     *         description="ID of the facility to be deleted",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Facility deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Facility deleted successfully"),
     *             @OA\Property(property="data", type="object", description="Deleted facility data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Facility not found"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function deleteCommunityFacility($facility_id)
    {
        try {
            $communityFacility = CommunityFacility::where('id', $facility_id)->first();

            if (!$communityFacility) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Facility not found',
                    'data' => (object) [],
                ], 404);
            }

            $facilityData['status'] = '0';
            $facilityData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            $communityFacility->update($facilityData);


            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Facility deleted successfully',
                'data' => $facilityData,
            ], 200);
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
}
