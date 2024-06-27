<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Follows;
use App\Models\Address;
use App\Models\Comment;
use App\Models\PostType;
use App\Models\PostLike;
use App\Models\ProfileInterest;
use App\Models\PostData;
use App\Models\CommunityDetail;
use App\Models\PostHashtag;
use App\Models\UserDetails;
use App\Models\HashtagMaster;
use Illuminate\Http\Request;
use Image;
use App\Models\Notification;
use Illuminate\Support\Str;
use JWTAuth, Validator,  Hash, URL, Helper ,File, Stripe, Session, Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
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
 * PS-4 Level: Search Hashtag.
 *
 * @param \Illuminate\Http\Request $request The HTTP request containing the hashtag name.
 *
 * @return \Illuminate\Http\JsonResponse The JSON response containing the search results.
 * @OA\Post(
 *     path="/api/search-post",
 *     summary="Suggestion Post",
 *     tags={"Search"},
 *     @OA\Response(
 *         response=200,
 *         description="Hashtag found successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="code", type="integer", example=200),
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="posts retrieved successfully"),
 *          )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="code", type="integer", example=500),
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
 *         )
 *     ),
 *     security={
 *         {"bearer_token": {}}
 *     }
 * )
 */
    public function suggestionPost() {
       try {
            $feeds = Post::inRandomOrder()->paginate(20);

            foreach ($feeds as $key => $post) {
                $loggedin_profile_id = auth()->user()->profile->id;

                $isFollowing = Follows::where('following_profile_id',$loggedin_profile_id)->where('followed_profile_id',$post->profile_id)->first();
                $post->isFollowing = $isFollowing ? 'true' : 'false';

                $postCreatedAt = $post->created_at;
                    $currentTime = Carbon::now();
                    $timeDifference = $postCreatedAt->diff($currentTime);
                    $totalHours = $timeDifference->h + ($timeDifference->days * 24);
                     if ($totalHours < 1) {
                        $post->timeDifference = $timeDifference->i . 'm';
                    }elseif($totalHours < 24) {
                            $post->timeDifference = $totalHours . 'h';
                    }elseif($timeDifference->days < 7) {   
                            $post->timeDifference = $timeDifference->days . 'd';
                    }elseif($timeDifference->days == 7) {   
                            $post->timeDifference = '1w';
                    }else{
                        $post->timeDifference = "";
                    }
                unset($post->created_at);
                unset($post->updated_at);
                unset($post->deleted_at);

                $isLiked = PostLike::select('like_flag')->where('profile_id',$loggedin_profile_id)->where('post_id',$post->id)->first();
                $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';
                $profile = $this->processObject($post->profile);
                 $userType = $post->profile && $post->profile->userType ? $this->processObject($post->profile->userType) : null;

                if ($post->profile && $post->profile->user) {
                    $user = $this->processObject($post->profile->user);
                    if ($user) {
          
                        $userDetail = UserDetails::where('profile_id', $user->profile->id)->first();
                        if ($userDetail) {
              
                            $user->profile_picture = $userDetail->profile_picture ?? null;
                        } else {
                            $user->profile_picture = null; 
                        }
                        unset($user->profile);
                    }
                }
                if ($post->profile && $post->profile->user_detail) {
                    $user_detail = $this->processObject($post->profile->user_detail);
                    if ($user_detail) {
                        $user_detail->profile_picture = $user_detail->profile_picture ?? null;
                    } 
                }

                    $CommunityDetail = CommunityDetail::where('profile_id',$profile->id)->first();
                    $post->profile->community_profile_id = $CommunityDetail->profile_id?? null;
                    $post->profile->name_of_community = $CommunityDetail->name_of_community?? null;
                    $post->profile->community_image = $CommunityDetail->community_image?? null;

                $comments = $post->comments ?? null;
                foreach ($post->comments as $comment) {
                    $comment = $this->processObject($comment);
                     if ($comment->profile && $comment->profile->user) {
                    $commentUser = $this->processObject($comment->profile->user)?? null;}
                    if ($comment->profile && $comment->profile->user) {
                    $commentUserDetail = $comment->profile->UserDetail;
                    $commentUser->profile_picture = $commentUserDetail->profile_picture ?? null;
                    unset($comment->profile->UserDetail);}
                }

                $findPostimage = $post->postRelatedData ?? null ;
                foreach ($post->postRelatedData as $postRelatedData) {
                    $postRelatedData = $this->processObject($postRelatedData);
                }
                $postHashtag = $post->postHashtag ?? null;
                foreach ($post->postHashtag as $postHashtag) {
                    $postHashtag = $this->processObject($postHashtag);
                    $hashtagName = $this->processObject($postHashtag->hashtag) ;
                }

                foreach ($post->postLikes as $like) {
                    $like = $this->processObject($like);
                     if ($like->profile && $like->profile->user) {
                    //$likeUser = $this->processObject($like->profile->user);}
                    $likeUser = $this->processObject($like->profile);}
                     if ($like->profile && $like->profile->UserDetail) {
                    $likeUserDetail = $like->profile->UserDetail;

                    $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
                    unset($like->profile->UserDetail);}
                }
                $postlikeCount =$post->postLikes ?? null;
                $post->commentCount = $post->comments->count();
                $post->likeCount = $post->postLikes->count();
            }
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'posts retrieved successfully',
                'data' => $feeds,
            ], 200);
	    } 
        catch (\Exception $e) {
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
     * PS-4 Level: Search Hashtag.
     *
     * @param int $post_id The ID of the post to search.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the search results.
     *
     * @OA\Get(
     *     path="/api/search-post/{postId}",
     *     summary="Search Post",
     *     tags={"Search"},
     *     @OA\Parameter(
     *         name="postId",
     *         in="path",
     *         description="ID of the post to search",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hashtag found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Posts retrieved successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *        )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function searchPost($post_id) {
       try {
            $searchedPost = Post::where("id", $post_id)->get();
            $feeds = Post::inRandomOrder()->get();
            $feedCollection = new Collection($feeds);
            $mergedPosts = $searchedPost->merge($feedCollection);
            $perPage = 10;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $offset = ($page - 1) * $perPage;
            $items = $mergedPosts->slice($offset, $perPage)->all();
            $items = array_values($items);
            $postsPaginated = new LengthAwarePaginator($items, count($mergedPosts), $perPage, $page);

            foreach ($postsPaginated as $key => $post) {
                $loggedin_profile_id = auth()->user()->profile->id;

                $isFollowing = Follows::where('following_profile_id',$loggedin_profile_id)->where('followed_profile_id',$post->profile_id)->first();
                $post->isFollowing = $isFollowing ? 'true' : 'false';

                $postCreatedAt = $post->created_at;
                    $currentTime = Carbon::now();
                    $timeDifference = $postCreatedAt->diff($currentTime);
                    $totalHours = $timeDifference->h + ($timeDifference->days * 24);
                     if ($totalHours < 1) {
                        $post->timeDifference = $timeDifference->i . 'm';
                    }elseif($totalHours < 24) {
                            $post->timeDifference = $totalHours . 'h';
                    }elseif($timeDifference->days < 7) {   
                            $post->timeDifference = $timeDifference->days . 'd';
                    }elseif($timeDifference->days == 7) {   
                            $post->timeDifference = '1w';
                    }else{
                        $post->timeDifference = "";
                    }
                unset($post->created_at);
                unset($post->updated_at);
                unset($post->deleted_at);

                $post->share = 'https://ishtdev.netlify.app/post-share/' . $post->id;
                $isLiked = PostLike::select('like_flag')->where('profile_id',$loggedin_profile_id)->where('post_id',$post->id)->first();
                $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';
                $profile = $this->processObject($post->profile);
                 $userType = $post->profile && $post->profile->userType ? $this->processObject($post->profile->userType) : null;

                if ($post->profile && $post->profile->user) {
                    $user = $this->processObject($post->profile->user);
                    if ($user) {
          
                        $userDetail = UserDetails::where('profile_id', $user->profile->id)->first();
                        if ($userDetail) {
              
                            $user->profile_picture = $userDetail->profile_picture ?? null;
                        } else {
                            $user->profile_picture = null; 
                        }
                        unset($user->profile);
                    }
                }
                if ($post->profile && $post->profile->user_detail) {
                    $user_detail = $this->processObject($post->profile->user_detail);
                    if ($user_detail) {
                        $user_detail->profile_picture = $user_detail->profile_picture ?? null;
                    } 
                }

                    $CommunityDetail = CommunityDetail::where('profile_id',$profile->id)->first();
                    $post->profile->community_profile_id = $CommunityDetail->profile_id?? null;
                    $post->profile->name_of_community = $CommunityDetail->name_of_community?? null;
                    $post->profile->community_image = $CommunityDetail->community_image?? null;

                $comments = $post->comments ?? null;
                foreach ($post->comments as $comment) {
                    $comment = $this->processObject($comment);
                     if ($comment->profile && $comment->profile->user) {
                    $commentUser = $this->processObject($comment->profile->user)?? null;}
                    if ($comment->profile && $comment->profile->user) {
                    $commentUserDetail = $comment->profile->UserDetail;
                    $commentUser->profile_picture = $commentUserDetail->profile_picture ?? null;
                    unset($comment->profile->UserDetail);}
                }

                $findPostimage = $post->postRelatedData ?? null ;
                foreach ($post->postRelatedData as $postRelatedData) {
                    $postRelatedData = $this->processObject($postRelatedData);
                }
                $postHashtag = $post->postHashtag ?? null;
                foreach ($post->postHashtag as $postHashtag) {
                    $postHashtag = $this->processObject($postHashtag);
                    $hashtagName = $this->processObject($postHashtag->hashtag) ;
                }

                foreach ($post->postLikes as $like) {
                    $like = $this->processObject($like);
                     if ($like->profile && $like->profile->user) {
                    //$likeUser = $this->processObject($like->profile->user);}
                    $likeUser = $this->processObject($like->profile);}
                     if ($like->profile && $like->profile->UserDetail) {
                    $likeUserDetail = $like->profile->UserDetail;

                    $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
                    unset($like->profile->UserDetail);}
                }
                $postlikeCount =$post->postLikes ?? null;
                $post->commentCount = $post->comments->count();
                $post->likeCount = $post->postLikes->count();
            }
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'posts retrieved successfully',
                'data' => $postsPaginated,
            ], 200);

	    } 
        catch (\Exception $e) {
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
     * Search for communities based on the provided name.
     *
     * @OA\Post(
     *     path="/api/search-community",
     *     tags={"Search"},
     *     summary="Search for communities",
     *     description="Search for communities based on the provided name.",
     *     operationId="searchCommunity",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Search value",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="CommunityName"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All communities retrieved successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
     *         ),
     *     ),
     * )
     */
    public function searchCommunity(Request $request){
        try{
             $searchValue = $request->name; 
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            if (strpos($searchValue, '%') !== false || strpos($searchValue, '_')) {
            return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'All community retrived successfully',
                    'data' => [],
                ]);
            }
            if($request->latitude && $request->longitude){
                    $existingRecord =CommunityDetail::select(
                    'id',
                    'profile_id',
                    'status',
                    'name_of_community',
                    'community_image',
                    'community_image_background',
                    'latitude',
                    'longitude',
                    'short_description',
                    'location_address',
                    \DB::raw("(6373 * acos(cos(radians($latitude))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians($longitude))
                    + sin(radians($latitude)) * sin(radians(latitude)))) AS distance_in_km")
                )
                ->whereRaw("status IN ('approved', 'approved_with_tick')")
                ->whereNotNull('latitude')
                ->whereRaw("name_of_community LIKE '%{$searchValue}%'")
                ->orderBy('distance_in_km', 'ASC')->get();

                $communityData = [];
                foreach ($existingRecord as $community) {
                    $communityData[] = [
                        'id' => $community->id,
                        'name' => $community->name_of_community,
                        'profile_id' => $community->profile_id,
                        'status' => $community->status,
                        'community_image' => $community->community_image,
                        'community_image_background' => $community->community_image_background,
                        'short_description' => $community->short_description,
                        'latitude' => $community->latitude,
                        'longitude' => $community->longitude,
                        'distance_in_km' => $community->distance_in_km,
                        'location_address' => $community->location_address,
                    ];
                }
            }else{
                $existingRecord = CommunityDetail::select(
                        'id',
                        'profile_id',
                        'status',
                        'name_of_community',
                        'community_image',
                        'community_image_background',
                        'latitude',
                        'longitude',
                        'short_description',
                        'location_of_community'
                    )
                    ->whereRaw("status IN ('approved', 'approved_with_tick')")
                    ->where(function ($query) use ($searchValue) {
                        $query->whereRaw("name_of_community LIKE '%{$searchValue}%'")
                            ->orWhereRaw("location_of_community LIKE '%{$searchValue}%'");
                    })
                    ->get();

                $searchWithCity = Address::where('city', $request->name)->with('communityDetail')->get();

                $communityData = [];
                foreach ($existingRecord as $community) {
                    $communityData[] = [
                        'id' => $community->id,
                        'name' => $community->name_of_community,
                        'profile_id' => $community->profile_id,
                        'status' => $community->status,
                        'community_image' => $community->community_image,
                        'community_image_background' => $community->community_image_background,
                        'short_description' => $community->short_description,
                        'latitude' => $community->latitude,
                        'longitude' => $community->longitude,
                        'distance_in_km' => null,
                        'location_of_community' => $community->location_of_community,
                    ];
                }
            }
            
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All communities retrieved successfully',
                            'data' => $communityData, 
                        ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }

    public function searchUser($name){
        try{
            if (strpos($name, '%') !== false || strpos($name, '_')) {
            return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'All user retrived successfully',
                    'data' => [],
                ]);}
                $existingRecord = UserDetails::select(
                    'profile_id', 'full_name', 'become_pandit', 'email', 'dob', 'religion',
                    'varna', 'profile_picture', 'kul_devta_devi', 'ishtdev', 'gotra', 'bio', 
                    'kyc_details_doc01', 'kyc_details_doc02', 'verified', 'is_business_profile'
                    )
                    ->where(function ($query) use ($name) {
                        $query->whereRaw("full_name LIKE '{$name}%'");
                    })
                    ->get();
                $userData = [];
                foreach ($existingRecord as $user) {
                    $userData[] = [
                        
                        'profile_id' => $user->profile_id,
                        'full_name' => $user->full_name,
                        'profile_picture' => $user->profile_picture,
                        'bio' => $user->bio,
                        'become_pandit' => $user->become_pandit,
                        'email' => $user->email,
                        'dob' => $user->dob,
                        'religion' => $user->religion,
                        'varna' => $user->varna,
                        'gotra' => $user->gotra,
                        'kul_devta_devi' => $user->kul_devta_devi,
                        'ishtdev' => $user->ishtdev,
                        'kyc_doc_front' => $user->kyc_details_doc01,
                        'kyc_doc_back' => $user->kyc_details_doc02,
                        'verified' => $user->verified,
                        'is_business_profile' => $user->is_business_profile,
                    ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All user retrieved successfully',
                            'data' => $userData, 
                        ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }

    public function nearByCommunity(Request $request)
    {
        try { 
            $profileId = $request->profile_id;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $existingRecord = CommunityDetail::select(
                'id',
                'profile_id',
                'status',
                'name_of_community',
                'community_image',
                'community_image_background',
                'latitude',
                'longitude',
                'short_description',
                'location_address',
                \DB::raw("(6373 * acos(cos(radians($latitude))
                * cos(radians(latitude))
                * cos(radians(longitude) - radians($longitude))
                + sin(radians($latitude)) * sin(radians(latitude)))) AS distance_in_km")
            )
            ->whereRaw("status IN ('approved', 'approved_with_tick')")
            ->whereNotNull('latitude')
            ->having('distance_in_km', '<=', 0.5)
            ->orderBy('distance_in_km', 'ASC')
            ->get();

            $communityData = [];
            foreach ($existingRecord as $community) {
                $communityData[] = [
                    'id' => $community->id,
                    'name' => $community->name_of_community,
                    'profile_id' => $community->profile_id,
                    'status' => $community->status,
                    'community_image' => $community->community_image,
                    'community_image_background' => $community->community_image_background,
                    'short_description' => $community->short_description,
                    'latitude' => $community->latitude,
                    'longitude' => $community->longitude,
                    'distance_in_km' => $community->distance_in_km,
                    'location_address' => $community->location_address,
                ];
            }
            
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All communities retrieved successfully',
                'data' => $communityData, 
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
