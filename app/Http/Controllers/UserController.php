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

use App\Models\CommunityDetail;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Wishlist;
use App\Models\PostReport;
use App\Models\PostLike;
use App\Models\Follows;
//use App\Image;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use Carbon\Carbon;
class UserController extends Controller
{
    private function processObject($object)
    {
        $fieldsToRemove = ['password', 'created_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
        if (!empty($fieldsToRemove)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }
        }
        return $object;
    }

    /**
     * PS-4 Level: Update User Type.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the user details.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success or failure of the user type update.
     * @OA\Post(
     *     path="/api/updateUserType",
     *     summary="Update User Type",
     *       tags={"Admin"},
     *     description="Endpoint to update the user type.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User type update details",
     *         @OA\JsonContent(
     *             required={"user_id", "profile_id", "user_type"},
     *             @OA\Property(property="user_id", type="integer", format="number", example=1, description="User ID"),
     *             @OA\Property(property="profile_id", type="integer", format="number", example=2, description="Profile ID"),
     *             @OA\Property(property="user_type", type="integer", format="number", example=3, description="User Type ID"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User type updated successfully"),
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
    public function updateUserType(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'profile_id' => 'required',
                'become_pandit' => 'required',
                'rejection_reason' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $validatedData = $request->only(['user_id', 'profile_id', 'become_pandit', 'rejection_reason']);

            $existingDetail = UserDetails::where('profile_id', $validatedData['profile_id'])->first();
            if ($existingDetail && $existingDetail->become_pandit === 'approved' && $validatedData['become_pandit'] === 'pending') {
                return response()->json([
                    'code' => 400,
                    'status' => 'success',
                    'message' => 'Status cannot be Pending',
                    'data' => [],
                ], 400);
            }

            UserDetails::where('id', $validatedData['user_id'])
                ->where('profile_id', $validatedData['profile_id'])
                ->update(['become_pandit' => $validatedData['become_pandit']]);

            if ($validatedData['become_pandit'] === 'rejected' || $validatedData['become_pandit'] === 'block') {
                if (!isset($validatedData['rejection_reason'])) {
                    return response()->json([
                        'code' => 400,
                        'status' => 'failure',
                        'message' => 'please provide rejection reson',
                    ], 400);
                }
                $userDetailsUpdated = UserDetails::where('id', $validatedData['user_id'])
                    ->where('profile_id', $validatedData['profile_id'])
                    ->update(['rejection_reason' => $validatedData['rejection_reason']]);
                if (!$userDetailsUpdated) {
                    return response()->json([
                        'code' => 400,
                        'status' => 'failure',
                        'message' => 'Failed to update user details',
                    ], 400);
                }
            }

            if ($validatedData['become_pandit'] == 'approved') {
                $profileUpdated = Profile::where('id', $validatedData['profile_id'])
                    ->where('user_id', $validatedData['user_id'])
                    ->update(['user_type_id' => '2']);

                $userDetailsUpdated = UserDetails::where('id', $validatedData['user_id'])
                    ->where('profile_id', $validatedData['profile_id'])
                    ->update(['rejection_reason' => null]);

                if (!$profileUpdated) {
                    return response()->json([
                        'code' => 400,
                        'status' => 'failure',
                        'message' => 'Failed to update profile',
                    ], 400);
                }
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Pandit status updated successfully',
                'data' => $validatedData,
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
     * Update User Type.
     *
     * PS-4 Level: This endpoint is used to update the user type for a community profile.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the user type update details.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the success or failure of the user type update.
     *
     * @OA\Post(
     *     path="/api/updateCommunityStatus",
     *     summary="Update User Type",
     *     tags={"Admin"},
     *     description="Endpoint to update the user type.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User type update details",
     *         @OA\JsonContent(
     *             required={"profile_id", "status", "rejection_reason"},
     *             @OA\Property(property="profile_id", type="integer", format="number", example=2, description="Profile ID"),
     *             @OA\Property(property="status", type="string", example="rejected", description="New status for the community"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community status updated successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Community does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Community does not exist"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while processing the request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *          ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function updateCommunityStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
                'status' => 'required',
                'rejection_reason' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $validatedData = $request->only(['profile_id', 'status']);
            $checkCommunity = CommunityDetail::where('profile_id', $validatedData['profile_id'])->first();

            if (!$checkCommunity) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Community does not exists',
                    'data' => array(),
                ], 200);
            }

            if (strtolower($validatedData['status']) == 'rejected' || strtolower($validatedData['status']) == 'block') {
                if (!$request->rejection_reason) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Rejection reason is mandatory',
                        'data' => array(),
                    ], 404);
                }

                CommunityDetail::where('profile_id', $validatedData['profile_id'])
                    ->update(['status' => $validatedData['status'], 'rejection_reason' => $request->rejection_reason]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community status updated successfully',
                    'data' => array(),
                ], 200);
            } elseif (strtolower($validatedData['status']) == 'approved' || strtolower($validatedData['status']) == 'pending') {
                CommunityDetail::where('profile_id', $validatedData['profile_id'])
                    ->update(['status' => $validatedData['status']]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community status updated successfully',
                    'data' => array(),
                ], 200);
            } else {
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Provide a valid status',
                    'data' => array(),
                ], 400);
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

    public function showAllUsers()
    {
        try {
            $users = UserDetails::get();
            if ($users->count() === 0) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No user found',
                    'data' => [],
                ], 404);
            }
            $allUser = [];
            foreach ($users as $user) {
                $rejection_reason = ($user->status == 'approved' || $user->status == 'pending') ? '' : $user->rejection_reason;

                $allUser[] = [
                    'id' => $user->id,
                    'profile_id' => $user->profile_id,
                    'full_name' => ucfirst($user->full_name),
                    'become_pandit' => $user->become_pandit,
                    'rejection_reason' => $rejection_reason,
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All User retreived Successfully',
                'data' => $allUser,
            ], 200);
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

    public function showAllWishlist()
    {
        try {
            $allWishlist = Wishlist::get();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All Wishlist retreived Successfully',
                'data' => $allWishlist,
            ], 200);
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

    public function showAllBusinesslist()
    {
        try {
            $allbusinesslist = UserDetails::whereNotNull('business_doc')->get();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All Businesslist retreived Successfully',
                'data' => $allbusinesslist,
            ], 200);
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

    public function greenTickRequest()
    {
        try {
            $users = UserDetails::whereNotNull('doc_name')->get();
            if ($users->count() === 0) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No user found',
                    'data' => [],
                ], 404);
            }
            $allUser = [];
            foreach ($users as $user) {
                $rejection_reason = ($user->status == 'approved' || $user->status == 'pending') ? '' : $user->rejection_reason;

                $allUser[] = [
                    'id' => $user->id,
                    'profile_id' => $user->profile_id,
                    'full_name' => ucfirst($user->full_name),
                    'doc_name' => ucfirst($user->doc_name),
                    'verification_status' => $user->verification_status,
                ];
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All User retreived Successfully',
                'data' => $allUser,
            ], 200);
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

    // public function getAllReportedPost()
    // {
    //     try {
    //         $reportedPost = PostReport::with([
    //             'post' => function ($query) {
    //                 $query->select('id', 'caption', 'post_type', 'profile_id', 'boost_status', 'name_of_interest', 'city')
    //                     ->with(['postRelatedData' => function ($query) {
    //                         $query->select('id', 'post_id', 'post_data');
    //                     }]);
    //             },
    //             'profile' => function ($query) {
    //                 $query->select('id', 'user_type_id')
    //                     ->with([
    //                         'UserDetail' => function ($query) {
    //                             $query->select('id', 'full_name', 'profile_id');
    //                         },
    //                         'communityDetail' => function ($query) {
    //                             $query->select('id', 'name_of_community', 'short_description', 'profile_id');
    //                         }
    //                     ]);
    //             }
    //         ])->get();

    //         return response()->json([
    //             'code' => 200,
    //             'status' => 'success',
    //             'message' => 'Reported Post Retrieved Successfully',
    //             'data' => $this->processObject($reportedPost),
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'code' => 500,
    //             'status' => 'error',
    //             'message' => 'An error occurred while processing the request.',
    //             'errors' => [$e->getMessage()],
    //             'data' => [], 
    //         ], 500);
    //     }
    //  }



    public function getAllReportedPost()
    {
        try {
            // $reportedPosts = PostReport::with('post')->select('post_id', \DB::raw('count(*) as report_count'))
            //     ->with([
            //         'post' => function ($query) {
            //             $query->select('id', 'caption', 'post_type', 'profile_id', 'boost_status', 'name_of_interest', 'city')
            //                 ->with(['postRelatedData' => function ($query) {
            //                     $query->select('id', 'post_id', 'post_data');
            //                 }]);
            //         }
            //     ])
            //     ->groupBy('post_id')
            //     ->get();

            $reportedPosts = PostReport::with([
                'post' => function ($query) {
                    $query->withTrashed()->select('id', 'caption', 'post_type', 'profile_id', 'boost_status', 'name_of_interest', 'city', 'deleted_at')
                        ->with([
                            'postRelatedData' => function ($query) {
                                $query->select('id', 'post_id', 'post_data');
                            }
                        ]);
                }
            ])
                ->select('post_id', \DB::raw('count(*) as report_count'))
                ->groupBy('post_id')
                ->get();

            // echo"<pre>"; print_r($this->processObject($reportedPosts->toArray()));die;

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Reported Post Retrieved Successfully',
                'data' => $this->processObject($reportedPosts),
            ]);
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


    public function getReportedPost($id)
    {
    //   echo $id; die;
        try {
            $loggedin_profile_id = auth()->user()->profile->id;
            $isLiked = PostLike::select('like_flag')->where('profile_id', $loggedin_profile_id)->where('post_id', $id)->first();
            $post = Post::withTrashed()->find($id);

            if (!$post) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Post not found',
                    'data' => [],
                ], 404);
            }
            $post->share = 'https://ishtdevprod.netlify.app/post-share/' . $post->id;
            $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';

            $isFollowing = Follows::where('following_profile_id', $loggedin_profile_id)->where('followed_profile_id', $post->profile->id)->first();
            $post->isFollowing = $isFollowing ? 'true' : 'false';

        
            $postCreatedAt = $post->created_at;
            $currentTime = Carbon::now();
            $timeDifference = $postCreatedAt->diff($currentTime);
            $totalHours = $timeDifference->h + ($timeDifference->days * 24);
            if ($totalHours < 1) {
                $post->timeDifference = $timeDifference->i . 'm';
            } elseif ($totalHours < 24) {
                $post->timeDifference = $totalHours . 'h';
            } elseif ($timeDifference->days < 7) {
                $post->timeDifference = $timeDifference->days . 'd';
            } elseif ($timeDifference->days == 7) {
                $post->timeDifference = '1w';
            } else {
                $post->timeDifference = "";
            }
            $profile = $this->processObject($post->profile);
            $user = $this->processObject($post->profile->user);
            $userType = $this->processObject($post->profile->userType);
            $userDetail = $this->processObject($post->profile->UserDetail);

            $user->profile_picture = $userDetail->profile_picture ?? null;
            unset($post->profile->UserDetail);

            $comments = $post->comments ?? null;
            foreach ($post->comments as $comment) {
                $comment = $this->processObject($comment);
                $commentUser = $this->processObject($comment->profile->user);
                $commentUserDetail = $comment->profile->UserDetail;

                $commentUser->profile_picture = $commentUserDetail->profile_picture ?? null;
                unset($comment->profile->UserDetail);
            }

            $findPostimage = $post->postRelatedData ?? null;
            foreach ($post->postRelatedData as $postRelatedData) {
                $postRelatedData = $this->processObject($postRelatedData);
            }
            $postHashtag = $post->postHashtag ?? null;
            foreach ($post->postHashtag as $postHashtag) {
                $postHashtag = $this->processObject($postHashtag);
                $hashtagName = $this->processObject($postHashtag->hashtag);
            }

            foreach ($post->postLikes as $like) {
                $like = $this->processObject($like);
                $likeUser = $this->processObject($like->profile->user);
                $likeUserDetail = $like->profile->UserDetail;

                $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
                unset($like->profile->UserDetail);
            }
            $postlikeCount = $post->postLikes ?? null;
            $post->commentCount = $post->comments->count();
            $post->likeCount = $post->postLikes->count();

            $post->reportCount = $post->postReport->count();
            $postReport = $post->postReport ?? null;
            foreach ($post->postReport as $postReport) {
                $postReport = $this->processObject($postReport);
            }

            // echo"";print_r($post); die;

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Post retrieved successfully',
                'data' => [
                    'post' => $this->processObject($post),
                ],
            ], 200);
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
}

