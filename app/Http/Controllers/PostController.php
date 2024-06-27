<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Follows;
use App\Models\Comment;
use App\Models\PostType;
use App\Models\PostLike;
use App\Models\PostReport;
use App\Models\ProfileInterest;
use App\Models\PostData;
use App\Models\CommunityDetail;
use App\Models\UserPackage;
use App\Models\PostHashtag;
use App\Models\UserDetails;
use App\Models\HashtagMaster;
//use App\Image;
use Image;
use App\Models\Notification;
use Illuminate\Support\Str;
use JWTAuth, Validator,  Hash, URL, Helper ,File, Stripe, Session, Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PostController extends Controller
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
     * PS-4 Level: Show All Post.
     *
     * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
    *     path="/api/posts",
    *     summary="Retrieve posts based on user activity",
    *     tags={"Post"},
    *     description="Retrieve posts based on user activity, including posts from followed profiles and top-liked posts.",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Pagination details for retrieving posts",
    *         @OA\JsonContent(
    *             @OA\Property(property="page", type="integer", format="number", example=2, description="Page number (nullable)"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Posts retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="Posts retrieved successfully"),
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
    public function getPosts(Request $request)
	{
	    try {
            $userProfileId = auth()->user()->profile->id;
            $userInterests = ProfileInterest::where('Profile_id', $userProfileId)->pluck('interest_id')->toArray();
            $relatedPost = Post::where(function ($query) use ($userInterests) {
                foreach ($userInterests as $interest) {
                    $profileInterests = explode(',', $interest);
                    foreach ($profileInterests as $related) {
                        $query->orWhereRaw("FIND_IN_SET($related, interest_id)");
                    }
                }
            })->get();
            $followingIds = Follows::select('followed_profile_id')->where('following_profile_id', $userProfileId)->pluck('followed_profile_id')->toArray();
            $followingPosts = Post::whereIn('profile_id', $followingIds)->where(function ($query) use ($userInterests) {
                foreach ($userInterests as $interest) {
                    $profileInterests = explode(',', $interest);
                    foreach ($profileInterests as $related) {
                        $query->orWhereRaw("FIND_IN_SET($related, interest_id)");
                    }
                }
            })->get();

            $feeds = $followingPosts->concat($relatedPost)->shuffle();
            $feeds = new Collection($feeds);

            $page = Paginator::resolveCurrentPage() ?: 1;
            $perPage = 10;

            $offset = ($page - 1) * $perPage;

            $paginatedItems = $feeds->slice($offset, $perPage)->values()->all();

            $total = count($feeds);

            $feeds = new LengthAwarePaginator(
                $paginatedItems,
                $total,
                $perPage,
                $page,
                ['path' => Paginator::resolveCurrentPath()]
            );

            $feed = $feeds->count();
            if($feed < 10){
                $feeds = Post::inRandomOrder()->paginate(5);
            }
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

                $post->share = 'https://ishtdevprod.netlify.app/post-share/' . $post->id;
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
                            $user->verified = $userDetail->verified ?? null;
                        } else {
                            $user->profile_picture = null; 
                            $user->verified = null; 
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
                    $post->profile->status = $CommunityDetail->status?? null;

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
                $post->reportCount = $post->postReport->count();
                $postReport = $post->postReport ?? null;
                foreach ($post->postReport as $postReport) {
                    $postReport = $this->processObject($postReport);
                }
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
     * PS-4 Level: Add POST.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
    * @OA\Post(
    *     path="/api/post/store",
    *     summary="Create a new post",
    *     description="Create a new post",
        *     tags={"Post"},
    *     @OA\RequestBody(
    *         required=true,
    *         description="Details for creating a new post",
    *         @OA\JsonContent(
    *             @OA\Property(property="post_type", type="string", description="Type of the post", example="1"),
    *             @OA\Property(property="caption", type="string", description="Caption for the post", example="This is a post caption"),
    *             @OA\Property(property="post_data", type="array", description="Array of post data (file upload)", @OA\Items(type="string", format="binary")),
    *             @OA\Property(property="status", type="string", description="Status of the post", example="active"),
    *             @OA\Property(property="profile_id", type="integer", format="int32", description="ID of the user profile", example=1),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Post created successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="Post created successfully"),
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
	public function store(Request $request)
	{
	    try {
	        $validator = Validator::make($request->all(), [
	            'post_type' => 'required',
	            'caption' => 'required',
	            'post_data' => 'required',
                //'interest_name' => 'required',
                //'interest_id' => 'required',
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
	        $post->caption = $validatedData['caption'];
	        $post->name_of_interest = isset($request->interest_name) ? $request->interest_name : null;
            $post->interest_id = isset($request->interest_id) ? $request->interest_id : null;
            $post->city = isset($request->city) ? $request->city : null;
	        // $post->status = $validatedData['status'];
	        $post->profile_id = $validatedData['profile_id'];
	        $post->save();
            $id = $post->id ;

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

            $string = $validatedData['caption'] ;
            $pattern = '/#(\w+)/';
            if (preg_match_all($pattern, $string, $matches)) {
                $hashtags = $matches[0];
                foreach ($hashtags as $hashtag) {
                    $existingRecord = HashtagMaster::where('name',$hashtag)->first();
                    if (!$existingRecord) {
                        $newRecord = new HashtagMaster();
                        $newRecord->name = $hashtag;
                        $newRecord->save();
                        $hashtagId = $newRecord->id ;

                        $addPostHashtag = new PostHashtag();
                        $addPostHashtag->post_id = $id;
                        $addPostHashtag->hashtag_id = $hashtagId;
                        $addPostHashtag->save();
                    }else{
                        $hashtagId = $existingRecord->id ;

                        $addPostHashtag = new PostHashtag();
                        $addPostHashtag->post_id = $id;
                        $addPostHashtag->hashtag_id = $hashtagId;
                        $addPostHashtag->save();

                    }
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



      /**
     * PS-4 Level: Show Post With Post Id.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
      * @OA\Get(
    *     path="/api/post/{id}",
    *     summary="Retrieve a specific post by ID",
        *     tags={"Post"},
    *     description="Retrieve details of a specific post by providing its ID.",
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         description="ID of the post to retrieve",
    *         @OA\Schema(type="integer", format="int64"),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Post retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="Post retrieved successfully"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Post not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="Post not found"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An unexpected error occurred",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An unexpected error occurred"),
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
	public function show($id)
	{
	    try {
            $loggedin_profile_id = auth()->user()->profile->id;
            $isLiked = PostLike::select('like_flag')->where('profile_id',$loggedin_profile_id)->where('post_id',$id)->first();
            $post = Post::find($id);
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

                $isFollowing = Follows::where('following_profile_id',$loggedin_profile_id)->where('followed_profile_id',$post->profile->id)->first();
                $post->isFollowing = $isFollowing ? 'true' : 'false';

	            $post = $post;
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
			        $likeUser = $this->processObject($like->profile->user);
			        $likeUserDetail = $like->profile->UserDetail;

			        $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
		            unset($like->profile->UserDetail);
			    }
				$postlikeCount =$post->postLikes ?? null;
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

    public function shows($id)
	{
	    try {
            $post = Post::find($id);
            if (!$post) {
	            return response()->json([
	                'code' => 404,
	                'status' => 'failure',
	                'message' => 'Post not found',
	                'data' => [],
	            ], 404);
	        }
            $post->share = 'https://ishtdev.netlify.app/post-share/' . $post->id;
            
            $post = $post;
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
                $likeUser = $this->processObject($like->profile->user);
                $likeUserDetail = $like->profile->UserDetail;

                $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
                unset($like->profile->UserDetail);
            }
            $postlikeCount =$post->postLikes ?? null;
            $post->commentCount = $post->comments->count();
            $post->likeCount = $post->postLikes->count();


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

      /**
     * PS-4 Level: Show Post With Profile Id.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
      * @OA\Get(
    *     path="/api/user-posts/{profileid}",
    *     summary="Retrieve posts of a specific user by profile ID",
        *     tags={"Post"},
    *     description="Retrieve posts of a specific user by providing their profile ID.",
    *     @OA\Parameter(
    *         name="profileid",
    *         in="path",
    *         required=true,
    *         description="ID of the user's profile to retrieve posts",
    *         @OA\Schema(type="integer", format="int64"),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Posts retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="All posts retrieved successfully"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="No posts found for the user",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="No posts found"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An unexpected error occurred",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
	public function showUserPosts($profileid)
	{
	    try {
	        $logged_profile_id = auth()->user()->profile->id;
            $posts = Post::where('profile_id', $profileid)->whereNull('deleted_at');
            if ($logged_profile_id !== $profileid) {
                
               $pendingPostIds = UserPackage::where('profile_id', $profileid)
                                            ->where('status', 'pending')
                                            ->pluck('post_id');
                $posts = $posts->whereNotIn('id', $pendingPostIds);
            }

            $posts = $posts->orderBy('id', 'DESC')->get()->filter(function ($post) {
                return $post->postReport->count() < 3;
            })->values();

            // $posts = Post::where('profile_id', $profileid)
            //         ->orderBy('id', 'DESC')
            //         ->get()
            //         ->filter(function ($post) {
            //             return $post->postReport->count() < 3;
            //         })    ->values();

            $CommunityDetail = CommunityDetail::where('profile_id',$profileid)->first();
	        if ($posts->isEmpty()) {
	            return response()->json([
	                'code' => 404,
	                'status' => 'failure',
	                'message' => 'No posts found',
	                'data' => [],
	            ], 404);
	        }

	        foreach ($posts as $key => $post) {
                $post = $post;
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
                $loggedin_profile_id = auth()->user()->profile->id;

                $isFollowing = Follows::where('following_profile_id',$loggedin_profile_id)->where('followed_profile_id',$post->profile->id)->first();
                $post->isFollowing = $isFollowing ? 'true' : 'false';
                $post->share = 'https://ishtdevprod.netlify.app/post-share/' . $post->id;
                $isLiked = PostLike::select('like_flag')->where('profile_id',$loggedin_profile_id)->where('post_id',$post->id)->first();
                $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';
				$profile = $this->processObject($post->profile);
                if ($post->profile && $post->profile->user) {
				$user = $this->processObject($post->profile->user);}
             if (!empty($CommunityDetail)) {
                $post->profile->community_profile_id = $CommunityDetail->profile_id;
                $post->profile->name_of_community = $CommunityDetail->name_of_community;
                $post->profile->community_image = $CommunityDetail->community_image;
                $post->profile->status = $CommunityDetail->status;
            }
				$userType = $this->processObject($post->profile->userType);
                if ($post->profile->UserDetail) {
				$userDetail = $this->processObject($post->profile->UserDetail);
				$user->profile_picture = $userDetail->profile_picture ?? null;
                if($userDetail->make_profile_private == 'Yes' && $post->isFollowing == 'false' && $post->profile->id !== $loggedin_profile_id){
                    return response()->json([
                        'code' => 400,
                        'status' => 'failure',
                        'message' => 'This profile is private',
                        'data' => [],
                    ], 400);
                }
                unset($post->profile->UserDetail);
            }

			    $comments = $post->comments ?? null;
			    foreach ($post->comments as $comment) {
			        $comment = $this->processObject($comment);
                    if ($comment->profile && $comment->profile->user) {
			        $commentUser = $this->processObject($comment->profile->user);}
                    if ( $comment->profile && $comment->profile->UserDetail) {
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
                $post->reportCount = $post->postReport->count();
                $postReport = $post->postReport ?? null;
                foreach ($post->postReport as $postReport) {
                    $postReport = $this->processObject($postReport);
                }
	        }
	        return response()->json([
	            'code' => 200,
	            'status' => 'success',
	            'message' => 'All posts retrieved successfully',
	            'data' => $posts,
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
     * PS-4 Level: Show Post Type.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
    *     path="/api/showPostType",
    *     summary="Retrieve all post types",
        *     tags={"Post"},
    *     description="Retrieve all post types available in the system.",
    *     @OA\Response(
    *         response=200,
    *         description="All post types retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="All post types retrieved successfully"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An unexpected error occurred",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
    public function showPostType()
	{
	    try {
	        $postType = PostType::get();
            return response()->json([
	            'code' => 200,
	            'status' => 'success',
	            'message' => 'All posts type retrieved successfully',
	            'data' => $postType,
	        ], 200);
        }
        catch (\Exception $e) {

	        return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [], // Include any relevant data here
            ], 500);
	    }
    }

      /**
     * PS-4 Level: Like Post.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
      * @OA\Post(
    *     path="/api/likePost",
    *     summary="Like or unlike a post",
        *     tags={"Post"},
    *     description="Like or unlike a post based on the provided parameters.",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(property="profile_id", type="integer", description="ID of the user's profile", example=1),
    *                 @OA\Property(property="like_flag", type="integer", description="Like flag (1 for like, 0 for unlike)", example=1),
    *                 @OA\Property(property="post_id", type="integer", description="ID of the post to be liked or unliked", example=123),
    *             ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Post Liked Successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="Post Like Successfully"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Validation Error",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="Validation Error"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An unexpected error occurred",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
    public function likePost(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'profile_id'   => ['required', 'numeric'],
                'post_id' => ['required', 'numeric'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => [],
                ], 404);
            }
            $user_id = auth()->user()->id;
            $findProfile = Profile::where('id', $request->profile_id)->where('user_id',$user_id)->first();
            $findPost = Post::where('id', $request->post_id)->first();
            if(empty($findProfile->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User Profile not found',
                    'data' => array(),
            ]);
           }else if(empty($findPost->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post not found',
                    'data' => array(),
                ]);
           }
           $validatedData = $request->only(['profile_id', 'like_flag', 'post_id']);
           $findPreviousLike  = PostLike::where('post_id', $validatedData['post_id'])->where('profile_id', $validatedData['profile_id'])->first();
           if(!empty($findPreviousLike)){
                if($findPreviousLike->like_flag == 0){
                    $sendData = array("like_flag" => 1);
                }else{
                    $sendData = array("like_flag" => 0);
                }

                $findPreviousLike->update($sendData);

           }else{
            $postLike = new PostLike();
            $postLike->profile_id = $validatedData['profile_id'];
            $postLike->user_id = $user_id;
            $postLike->like_flag = 1;
            $postLike->post_id = $validatedData['post_id'];
            $postLike->save();
           }

           $postlikeCount = PostLike::where('post_id', $validatedData['post_id'])->where('like_flag', 1)->count();
           return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Post Like Successfully',
            'data' => array(
                "postlikeCount" => $postlikeCount,
                "likeData" => $findPreviousLike ? $findPreviousLike :  $postLike,
        ),
          ]);

        } catch (\Exception $e) {


	        return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [], // Include any relevant data here
            ], 500);
	    }

    }

      /**
     * PS-4 Level: Show Post Comments.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
    * @OA\Post(
    *     path="/api/getPostCommnet",
    *     summary="Get comments for a specific post",
        *     tags={"Post"},
    *     description="Retrieve comments for a specific post based on the provided post_id.",
    *     @OA\Parameter(
    *         name="post_id",
    *         in="query",
    *         required=true,
    *         description="ID of the post for which comments are to be retrieved",
    *         @OA\Schema(type="integer", example=123),
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="All posts comment retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="All posts comment retrieved successfully"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Validation Error",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="Validation Error"),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An unexpected error occurred",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
    public function getPostCommnet(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'post_id' => ['required', 'numeric'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => [],
                ], 404);
            }
            $id = $request->post_id;
            $findPost =$post = Post::find($id);
            $findPost = $this->processObject($post);
            if(empty($findPost->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post not found',
                    'data' => array(),
                ]);
               }
               $comments = $findPost->comments ?? null;
               foreach ($comments as $key => $comment) {
                   $comment = $this->processObject($comment);
			        $commentUser = $this->processObject($comment->profile->user);
			        $commentUserDetail = $comment->profile->UserDetail;

    				$commentUser->profile_picture = $commentUserDetail->profile_picture ?? null;
		            unset($comment->profile->UserDetail);
               }

               return response()->json([
	            'code' => 200,
	            'status' => 'success',
	            'message' => 'All posts commnet retrieved successfully',
	            'data' => $findPost,
	        ], 200);

        } catch (\Exception $e) {


	        return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An error occurred while processing the request.',
                'errors' => [$e->getMessage()],
                'data' => [], // Include any relevant data here
            ], 500);
	    }

    }

     /**
     * PS-4 Level: Show All Post by Hashtag.
     *
     * @param Request $request The request parameter.
     *
     * @return \Illuminate\Http\JsonResponse
    * @OA\Post(
    *     path="/api/getallpostbyHashtag",
    *     summary="Get Posts by Hashtag",
        *     tags={"Post"},
    *     description="Retrieve posts based on a specified hashtag in the post caption.",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Hashtag details for retrieving posts",
    *         @OA\JsonContent(
    *             @OA\Property(property="hashtag", type="string", description="Hashtag to search for in post captions", example="#example"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Posts retrieved successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=200),
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="message", type="string", example="All posts retrieved successfully"),
    *           
    *         ),
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="No posts found",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="No posts found"),
    *      
    *         ),
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="An error occurred while processing the request",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=500),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="An error occurred while processing the request"),
    *             
    *         ),
    *     ),
    *     security={
    *         {"bearer_token": {}}
    *     }
    * )
    */
    public function getallpostbyHashtag(Request $request){
        try {
            $validator = Validator::make($request->all(),[
                'hashtag' => ['required'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => [],
                ], 404);
            }
	        $posts = Post::orderBy('post.id', 'DESC')->where('caption', 'LIKE', '%' . $request->hashtag . '%')->get();

	        if ($posts->isEmpty()) {
	            return response()->json([
	                'code' => 404,
	                'status' => 'failure',
	                'message' => 'No posts found',
	                'data' => [],
	            ], 404);
	        }


			foreach ($posts as $key => $post) {

                // $caption = $post->caption ;
                // $hashtagToFind = $request->hashtag;

                //  // Define a regular expression pattern to find the specified hashtag
                // $pattern = "/$hashtagToFind/i"; // Use the 'i' flag for case-insensitive matching
                // // Use preg_match to find the hashtag
                // if (strpos($caption, $hashtagToFind) !== false) {

				$post = $this->processObject($post);
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
			        $likeUser = $this->processObject($like->profile->user);
			        $likeUserDetail = $like->profile->UserDetail;

			        $likeUser->profile_picture = $likeUserDetail->profile_picture ?? null;
		            unset($like->profile->UserDetail);
			    }
				$postlikeCount =$post->postLikes ?? null;
				$post->commentCount = $post->comments->count();
				$post->likeCount = $post->postLikes->count();
               //}
			}

	        return response()->json([
	            'code' => 200,
	            'status' => 'success',
	            'message' => 'All posts retrieved successfully',
	            'data' =>  $posts,
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
     * Delete a post by ID.
     *
     * @param int $post_id The ID of the post to be deleted.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the status of the delete operation.
     * 
     * @OA\Delete(
     *     path="/api/delete-post/{post_id}",
     *     summary="Delete Post",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="path",
     *         required=true,
     *         description="ID of the post to be deleted",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Post deleted successfully"),
     *             @OA\Property(property="data", type="object", description="Deleted post data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Post not found"),
     *             @OA\Property(property="data", type="object", description="Empty object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), description="Array of error messages"),
     *             @OA\Property(property="data", type="object", description="Empty object"),
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function deletePost($post_id){
        try{
            $post = Post::where('id', $post_id)->first();
            // $post_comment = Comment::where('post_id', $post_id)->first();
            // $post_data = PostData::where('post_id', $post_id)->first();
            // $post_hashtag = PostHashtag::where('post_id', $post_id)->first();
            // $post_like = PostLike::where('post_id', $post_id)->first();

            if(!$post) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Post not found',
                    'data' => (object)[],
                ], 404);
            }
            
            $postData['status'] = '0';
            $postData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            $post->update($postData);

            // if($post_comment){
            // $postCommentData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_comment->update($postCommentData);}
            
            // if($post_data){
            // $postDetails['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_data->update($postDetails);}

            // if($post_hashtag){
            // $postHashtagData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_hashtag->update($postHashtagData);}

            // if($post_like){
            // $postLikeData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_like->update($postLikeData);}

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Post deleted successfully',
                'data' => $postData,
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

    public function deletePostByAdmin($post_id){
        try{
            $post = Post::where('id', $post_id)->first();
            // $post_comment = Comment::where('post_id', $post_id)->first();
            // $post_data = PostData::where('post_id', $post_id)->first();
            // $post_hashtag = PostHashtag::where('post_id', $post_id)->first();
            // $post_like = PostLike::where('post_id', $post_id)->first();

            if(!$post) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Post not found',
                    'data' => (object)[],
                ], 404);
            }
            
            $postData['status'] = '0';
            $postData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            $postData['deleted_by'] = isset($data['deleted_by']) ? $data['deleted_by'] : 'admin';
            $post->update($postData);

            // if($post_comment){
            // $postCommentData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_comment->update($postCommentData);}
            
            // if($post_data){
            // $postDetails['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_data->update($postDetails);}

            // if($post_hashtag){
            // $postHashtagData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_hashtag->update($postHashtagData);}

            // if($post_like){
            // $postLikeData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
            // $post_like->update($postLikeData);}

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Post deleted successfully',
                'data' => $postData,
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

     public function reportPost(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'profile_id'   => ['required', 'numeric'],
                'post_id' => ['required', 'numeric'],
                'report_flag' => ['required', 'string'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => $validator->errors(),
                ], 404);
            }
            $findProfile = Profile::where('id', $request->profile_id)->first();
            $findPost = Post::where('id', $request->post_id)->first();
            if(empty($findProfile->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Profile not found',
                    'data' => (object) [],
            ]);
           }else if(empty($findPost->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post not found',
                    'data' => (object) [],
                ]);
           }
            $validatedData = $request->only(['profile_id', 'report_flag', 'post_id']);
            $findPreviousReport  = PostReport::where('post_id', $validatedData['post_id'])->where('profile_id',$validatedData['profile_id'])->first();
           if(!empty($findPreviousReport)){
              return response()->json([
                    'code' => 400,
                    'status' => 'success',
                    'message' => 'Post alredy reported',
                    'data' => (object) [],
                ]);
           }else{
            $postReport = new PostReport();
            $postReport->profile_id = $validatedData['profile_id'];
            $postReport->report_flag = 1;
            $postReport->post_id = $validatedData['post_id'];
            $postReport->save();
           }
           $deletePost = $findPost->postReport->count();
           if($deletePost >= 3){

                    $post = Post::where('id', $validatedData['post_id'])->first();
                    // $post_comment = Comment::where('post_id', $validatedData['post_id'])->first();
                    // $post_data = PostData::where('post_id', $validatedData['post_id'])->first();
                    // $post_hashtag = PostHashtag::where('post_id', $validatedData['post_id'])->first();
                    // $post_like = PostLike::where('post_id', $validatedData['post_id'])->first();

                    $postData['status'] = '0';
                    $postData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                    $post->update($postData);

                    // if($post_comment){
                    // $postCommentData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                    // $post_comment->update($postCommentData);}
                    
                    // if($post_data){
                    // $postDetails['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                    // $post_data->update($postDetails);}

                    // if($post_hashtag){
                    // $postHashtagData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                    // $post_hashtag->update($postHashtagData);}

                    // if($post_like){
                    // $postLikeData['deleted_at'] = isset($data['deleted_at']) ? $data['deleted_at'] : date('Y-m-d H:i:s');
                    // $post_like->update($postLikeData);}

                return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Post Deleted Successfully',
                'data' => (object)[],
                ]);
           }
           return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Post Reported Successfully',
            'data' => $this->processObject($postReport),
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

}
