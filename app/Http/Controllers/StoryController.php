<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Post;
use App\Models\Follows;
use App\Models\StoryViewRecord;
use App\Models\CommunityArti;
use App\Models\Profile;
use App\Models\Comment;
use App\Models\PostType;
use App\Models\PostLike;
use App\Models\ProfileInterest;
use App\Models\PostData;
use App\Models\CommunityDetail;
use App\Models\PostHashtag;
use App\Models\UserDetails;
use App\Models\HashtagMaster;
use Image;
use App\Models\Notification;
use Illuminate\Support\Str;
use JWTAuth, Validator,  Hash, URL, Helper ,File, Stripe, Session, Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class StoryController extends Controller
{
    private function processObject($object)
    {
        $fieldsToRemove = ['password','created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
        if (!empty($object)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }
        }
        return $object;
    }

    /**
     * PS-4 Level: Get Recent Community List.
     *
     * Retrieve the list of communities recently accessed by followed profiles.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\GET(
     *     path="/api/recentCommunityList",
     *     summary="Get Recent Community List",
     *     tags={"Story"},
     *     description="Retrieve the list of communities recently accessed by followed profiles.",
     *     security={
     *         {"bearer_token": {}}
     *     },
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No followed profiles or communities found"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
     *         ),
     *     ),
     * )
     */
     public function recentCommunityList()
    {
        try {
            $profileId = auth()->user()->profile->id;

            $followingProfileIds = Follows::where('following_profile_id', $profileId)
                ->pluck('followed_profile_id')
                ->toArray();

            if (empty($followingProfileIds)) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Not following any profiles',
                    'data' => [],
                ], 404);
            }

            $currentTime = now();
            $endTime = $currentTime->copy()->subHours(24);
            
            $recentProfileIds = Post::whereIn('profile_id', $followingProfileIds)
                ->where('created_at', '>=', $endTime)
                ->pluck('profile_id')
                ->toArray();

            if (empty($recentProfileIds)) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No recent activity from followed profiles',
                    'data' => [],
                ], 404);
            }

            $communityDetails = CommunityDetail::whereIn('profile_id', $recentProfileIds)
                ->orderByRaw("FIELD(profile_id, " . implode(',', $recentProfileIds) . ")")
                ->get();

            $userDetails = UserDetails::whereIn('profile_id', $recentProfileIds)
                ->orderByRaw("FIELD(profile_id, " . implode(',', $recentProfileIds) . ")")
                ->get();

            $profileDetails = $communityDetails->concat($userDetails)->shuffle();

            foreach ($profileDetails as $profileDetail) {
                $userType = Profile::select('ut.name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $profileDetail->profile_id)
                    ->first();

                $profileDetail->user_type = $userType->name ?? 'Unknown';

                $recentPost = Post::where('profile_id', $profileDetail->profile_id)
                    ->where('created_at', '>=', $endTime)
                    ->latest('created_at')
                    ->first();

                if ($recentPost) {
                    $isViewed = StoryViewRecord::where('viewer_profile_id', $profileId)
                        ->where('story_profile_id', $profileDetail->profile_id)
                        ->where('story_post_id', $recentPost->id)
                        ->exists();

                    $profileDetail->isViewed = $isViewed ? 'true' : 'false';
                } else {
                    $profileDetail->isViewed = 'false';
                }
            }

            $sortedProfileDetails = $profileDetails->sortBy(function ($profileDetail) {
                return $profileDetail->isViewed === 'true' ? 1 : 0;
            });

            if ($sortedProfileDetails->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Not following any communities',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Community retrieved successfully',
                'data' => $sortedProfileDetails->values()->all(),
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
     * PS-4 Level: Get Recent Community's Post.
     *
     * Retrieve recent posts from a specific community based on profile ID.
     *
     * @param int $profile_id The ID of the profile.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\GET(
     *     path="/api/recentCommunityPost/{profile_id}",
     *     summary="Get Recent Community's Post",
     *     tags={"Story"},
     *     description="Retrieve recent posts from a specific community based on profile ID.",
     *     @OA\Parameter(
     *         name="profile_id",
     *         in="path",
     *         required=true,
     *         description="ID of the profile",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Posts retrieved successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Post does not exist"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
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
    public function recentCommunityPost(Request $request)
    {
        try {
            $currentTime = now();
            $endTime = $currentTime->subHours(24);
            $posts = Post::where('created_at', '>=', $endTime)
                ->where('profile_id', $request->profile_id)
                ->orderBy('created_at', 'DESC')
                ->get();

            $CommunityDetail = CommunityDetail::where('profile_id', $request->profile_id)->first();
            if ($posts->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Post does not exist',
                    'data' => (object) [],
                ], 404);
            } else {
                $loggedin_profile_id = auth()->user()->profile->id;
                $post = $posts->first();
                $post->isViewed = $request->isViewed == 'true' ? 'true' : 'false';

                if($request->isViewed == 'true'){
                    $existingStoryView = StoryViewRecord::where('viewer_profile_id', $loggedin_profile_id)
                                        ->where('story_profile_id', $request->profile_id)
                                        ->where('story_post_id', $post->id)->first();
                    if(!$existingStoryView){
                        $storyView = new StoryViewRecord();
                        $storyView->viewer_profile_id = $loggedin_profile_id;
                        $storyView->story_profile_id = $request->profile_id;
                        $storyView->story_post_id = $post->id;
                        $storyView->save();
                    }
                }

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

                unset($post->created_at);
                unset($post->updated_at);
                unset($post->deleted_at);

                $isFollowing = Follows::where('following_profile_id', $loggedin_profile_id)->where('followed_profile_id', $post->profile->id)->first();
                $post->isFollowing = $isFollowing ? 'true' : 'false';
                $post->share = 'https://ishtdevprod.netlify.app/post-share/' . $post->id;
                $isLiked = PostLike::select('like_flag')->where('profile_id', $loggedin_profile_id)->where('post_id', $post->id)->first();
                $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';
                $profile = $this->processObject($post->profile);
                if ($post->profile && $post->profile->user) {
                    $user = $this->processObject($post->profile->user);
                }
                if (!empty($CommunityDetail)) {
                    $post->profile->community_profile_id = $CommunityDetail->profile_id;
                    $post->profile->name_of_community = $CommunityDetail->name_of_community;
                    $post->profile->community_image = $CommunityDetail->community_image;
                }
                $userType = $this->processObject($post->profile->userType);
                if ($post->profile->UserDetail) {
                    $userDetail = $this->processObject($post->profile->UserDetail);
                    $user->profile_picture = $userDetail->profile_picture ?? null;
                    if ($userDetail->make_profile_private == 'Yes' && $post->isFollowing == 'false' && $post->profile->id !== $loggedin_profile_id) {
                        return response()->json([
                            'code' => 400,
                            'status' => 'failure',
                            'message' => 'This profile is private',
                            'data' => (object) [],
                        ], 400);
                    }
                    unset($post->profile->UserDetail);
                }

                $comments = $post->comments ?? null;
                foreach ($post->comments as $comment) {
                    $comment = $this->processObject($comment);
                    if ($comment->profile && $comment->profile->user) {
                        $commentUser = $this->processObject($comment->profile->user);
                    }
                    if ($comment->profile && $comment->profile->UserDetail) {
                        $commentUserDetail = $comment->profile->UserDetail;
                        $commentUser->profile_picture = $commentUserDetail->profile_picture ?? null;
                        unset($comment->profile->UserDetail);
                    }
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
                    if ($like->profile && $like->profile->user) {
                        //$likeUser = $this->processObject($like->profile->user);}
                        $likeUser = $this->processObject($like->profile);
                    }
                    if ($like->profile && $like->profile->UserDetail) {
                        $likeUserDetail = $like->profile->UserDetail;

                        $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
                        unset($like->profile->UserDetail);
                    }
                }
                $postlikeCount = $post->postLikes ?? null;
                $post->commentCount = $post->comments->count();
                $post->likeCount = $post->postLikes->count();
                $post->reportCount = $post->postReport->count();
                $postReport = $post->postReport ?? null;
                foreach ($post->postReport as $postReport) {
                    $postReport = $this->processObject($postReport);
                }

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post retrieved successfully',
                    'data' => $post,
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

    public function uploadStory(Request $request)
	{
	    try {
	        $validator = Validator::make($request->all(), [
	            'post_type' => 'required',
	            'post_data' => 'required',
                'city' => 'nullable',
	            //'status' => 'required',
	            'profile_id' => 'required',
	        ]);

	        if ($validator->fails()) {
	            return response()->json([
	                'code' => 400,
	                'status' => 'failure',
	                'message' => 'Validation failed',
	                'errors' => $validator->errors(),
	            ], 400);
	        }
            $validatedData = $request->only(['post_type', 'caption', 'post_data',  'profile_id']);

	        $post = new Post();
	        $post->post_type = $validatedData['post_type'];
            $post->city = isset($request->city) ? $request->city : null;
	        $post->profile_id = $validatedData['profile_id'];
	        $post->save();
            $id = $post->id;

            if ($request->hasFile('post_data')) {
                foreach ($request->file('post_data') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(base_path().'/public/postImage/', $imageName);
                    $postData['post_data'] = 'postImage/' . $imageName;

                    $postDataCreate = new PostData();
                    $postDataCreate->post_id = $id;
                    $postDataCreate->post_data = $postData['post_data'];
                    $postDataCreate->save();
                }
            }

            $findPost = Post::find($id);
            $findPost = $this->processObject($findPost);
            $findPostimage = $findPost->postRelatedData ?? null ;
            foreach ($findPost->postRelatedData as $postRelatedData) {
                $postRelatedData = $this->processObject($postRelatedData);
            }

	        $response = [
	            'code' => 200,
	            'status' => 'success',
	            'message' => 'Post Created Successfully',
	            'data' => $findPost
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

}
