<?php

namespace App\Http\Controllers;

use App\Models\CommunityDetail;
use App\Models\Follows;
use App\Models\Package;
use App\Models\Post;
use App\Models\PostData;
use App\Models\PostLike;
use App\Models\ProfileInterest;
use App\Models\UserDetails;
use App\Models\UserPackage;
use App\Models\HashtagMaster;
use App\Models\PostHashtag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use PDF;
use Validator;

class PackageController extends Controller
{
    public function createPackage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
                'package_type' => 'nullable',
                'package_description' => 'nullable',
                'duration' => 'nullable',
                'amount' => 'nullable',
                'gst_in_percent' => 'nullable',
                'status' => 'nullable',
                'package_id' => 'nullable',
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

            if (!isset($data['package_id'])) {
                $packageExist = Package::where('package_type', $data['package_type'])->first();
                if ($packageExist) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Package Type already exist',
                        'data' => (object) [],
                    ], 404);
                } else {
                    $package = new Package();
                    $package->package_type = $data['package_type'];
                    $package->duration = $data['duration'];
                    $package->amount = $data['amount'];
                    $package->gst_in_percent = $data['gst_in_percent'];
                    $package->status = $data['status'];
                    $package->profile_id = $data['profile_id'];

                    $gst_amount = ($package->amount * $package->gst_in_percent) / 100;
                    $package->total = $package->amount + $gst_amount;

                    $package->save();
                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Package created successfully',
                        'data' => $package,
                    ], 200);
                }
            }
            $existingPackage = Package::where('id', $request->package_id)->first();
            if (!$existingPackage) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Package not found',
                    'data' => (object) [],
                ], 404);
            }
            $packageDetails['package_type'] = isset($data['package_type']) ? $data['package_type'] : $existingPackage->package_type;
            $packageDetails['duration'] = isset($data['duration']) ? $data['duration'] : $existingPackage->duration;
            $packageDetails['amount'] = isset($data['amount']) ? $data['amount'] : $existingPackage->amount;
            $packageDetails['gst_in_percent'] = isset($data['gst_in_percent']) ? $data['gst_in_percent'] : $existingPackage->gst_in_percent;
            $packageDetails['status'] = isset($data['status']) ? $data['status'] : $existingPackage->status;
            $packageDetails['package_description'] = isset($data['package_description']) ? $data['package_description'] : $existingPackage->package_description;

            $gst_amount = ($packageDetails['amount'] * $packageDetails['gst_in_percent']) / 100;
            $packageDetails['total'] = $packageDetails['amount'] + $gst_amount;
            $existingPackage->update($packageDetails);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Package updated successfully',
                'data' => $packageDetails,
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

    public function getPackageDetail($packageId)
    {
        try {
            $package = Package::where('id', $packageId)->first();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Package retrived successfully',
                'data' => $package,
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
    public function getPackage()
    {
        try {
            $package = Package::get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Package retrived successfully',
                'data' => $package,
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

     public function boostPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
                'post_type' => 'nullable',
                'post_data' => 'required',
                'package_id' => 'nullable',
                'caption' => 'nullable',
                'city' => 'nullable',
                'post_id' => 'nullable',
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

            if(isset($data['post_id'])){
                $existingPost = Post::find($data['post_id']);
                $postDetail['post_type'] = $data['post_type'];
                $postDetail['caption'] = $data['caption'];
                $postDetail['city'] = isset($request->city) ? $request->city : null;
                $existingPost->update($postDetail);

                $string = $data['caption'] ;
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
                            $addPostHashtag->post_id = $data['post_id'];
                            $addPostHashtag->hashtag_id = $hashtagId;
                            $addPostHashtag->save();
                        }else{
                            $hashtagId = $existingRecord->id ;

                            $addPostHashtag = new PostHashtag();
                            $addPostHashtag->post_id = $data['post_id'];
                            $addPostHashtag->hashtag_id = $hashtagId;
                            $addPostHashtag->save();
                        }
                    }
                }
                if ($request->hasFile('post_data')) {
                    foreach ($request->file('post_data') as $image) {
                        $imageName = time() . '_' . $image->getClientOriginalName();
                        $image->move(base_path() . '/public/postImage/', $imageName);
                        $postData['post_data'] = 'postImage/' . $imageName;

                        $existingPostData = PostData::find($data['post_id']);
                        $postDataDetail['post_data'] = $postData['post_data'];
                        $existingPostData->update($postDataDetail);
                    }
                }
                $userPackage = UserPackage::where('post_id', $data['post_id'])->update(['status' => 'pending']);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post Updated Successfully',
                    'data' => $existingPost,
                ], 200);

            }else{
                $post = new Post();
                $post->post_type = $data['post_type'];
                $post->caption = $data['caption'];
                $post->city = isset($request->city) ? $request->city : null;
                $post->profile_id = $data['profile_id'];
                $post->save();
                $id = $post->id;

                $string = $data['caption'] ;
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
                $packageDetails = Package::where('id', $data['package_id'])->first();

                $package = new UserPackage();
                $package->profile_id = $data['profile_id'];
                $package->package_type = $packageDetails['package_type'];
                $package->package_description = $packageDetails['package_description'];
                $package->duration = $packageDetails['duration'];
                $package->amount = $packageDetails['amount'];
                $package->gst_in_percent = $packageDetails['gst_in_percent'];
                $package->total = $packageDetails['total'];
                $package->post_id = $id;
                $package->status = 'pending';
                $package->save();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Applied to Boost Post',
                    'data' => $package,
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

    public function changeStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'boost_id' => 'required',
                'status' => 'required',
                'rejected_reason' => 'nullable',
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
            $boostRequest = UserPackage::where('id', $data['boost_id'])->first();
            if (!$boostRequest) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Request not found',
                    'data' => (object) [],
                ], 404);
            }
            $boostDetails['rejected_reason'] = isset($data['rejected_reason']) ? $data['rejected_reason'] : $boostRequest->rejected_reason;
            $boostDetails['status'] = isset($data['status']) ? $data['status'] : $boostRequest->status;
            $boostRequest->update($boostDetails);

            if (isset($data['status'])) {
                $boostPost = Post::where('id', $boostRequest->post_id)->first();
                if (!$boostPost) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Post not found',
                        'data' => (object) [],
                    ], 404);
                }

                $boostPostDetails['boost_status'] = $data['status'] == 'approved' ? '1' : '0';
                $createdDate = \Carbon\Carbon::now();
                if($data['status']=='approved'){
                    $createdDate = \Carbon\Carbon::now();
                    $duration = $boostRequest->duration;

                    switch (strtolower($duration)) {
                        case 'daily':
                            $validityPeriod = 1;
                            break;
                        case 'weekly':
                            $validityPeriod = 7;
                            break;
                        case 'monthly':
                            $validityPeriod = 30;
                            break;
                        default:
                            $validityPeriod = 0;
                            break;
                    }

                    $expiryDate = $createdDate->addDays($validityPeriod);
                    $expiryDateString = $expiryDate->toDateString();
                    $boostPostDetails['start_date'] = now();
                    $boostPostDetails['end_date'] = $expiryDateString;
                    $boostPost->update($boostPostDetails);
                }

            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Status Updated Successfully',
                'data' => $boostRequest,
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

    public function getUserTransaction($profileId)
    {
        try {
            $transaction = UserPackage::where('profile_id', $profileId)
                ->with(['postDetail' => function ($query) {
                    $query->select('id', 'post_type', 'city', 'boost_status', 'end_date')
                        ->with(['postRelatedData' => function ($query) {
                            $query->select('id', 'post_id', 'post_data');
                        }]);
                }])
                ->with(['profileDetail' => function ($query) {
                    $query->select('id', 'profile_id', 'full_name', 'profile_picture', 'verified', 'become_pandit');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            $transaction->each(function ($transaction) {
                $transaction->makeHidden('updated_at');
            });
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'User Transactions retrived Successfully',
                'data' => $transaction,
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

    public function getBoostRequest()
    {
        try {
            $transaction = UserPackage::with(['profileDetail' => function ($query) {
                    $query->select('id', 'profile_id', 'full_name', 'profile_picture', 'verified', 'become_pandit');
                }])->with(['postDetail' => function ($query) {
                $query->select('id', 'post_type', 'city', 'boost_status', 'end_date')
                    ->with(['postRelatedData' => function ($query) {
                        $query->select('id', 'post_id', 'post_data');
                    }]);
            }])
                ->get();

            $transaction->each(function ($transaction) {
                $transaction->makeHidden('updated_at');
            });
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All Boost Request retrived Successfully',
                'data' => $transaction,
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

   public function getBoostRequestDetail($boostId)
    {
        try {
            $boostRequest = UserPackage::where('id', $boostId)

                ->with(['postDetail' => function ($query) {
                    $query->select('id', 'post_type', 'city', 'boost_status', 'end_date')
                        ->with(['postRelatedData' => function ($query) {
                            $query->select('id', 'post_id', 'post_data');
                        }]);
                }])
                ->with(['profileDetail' => function ($query) {
                    $query->select('id', 'profile_id', 'bio', 'full_name', 'profile_picture', 'verified', 'become_pandit', 'business_address', 'gst_number', 'business_name');
                }])->get();

            $boostRequest->each(function ($boostRequest) {
                $boostRequest->makeHidden('updated_at');
            });
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'User Transactions retrived Successfully',
                'data' => $boostRequest,
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

    public function getMyBoostPost($userProfileId)
    {
        try {
            // changed by md
            // $userPackages = UserPackage::where('profile_id', $userProfileId)
            //     ->with('postDetail.postRelatedData')
            //     ->with('profileDetail')
            //     ->get();
            $userPackages = UserPackage::where('profile_id', $userProfileId)
                ->whereHas('postDetail')
                ->with('postDetail.postRelatedData')
                ->with('profileDetail')
                ->orderBy('created_at', 'desc')
                ->get();


            $filteredPackages = $userPackages->map(function ($package) {
                $postDetail = $package->postDetail;
                $postRelatedData = $postDetail ? $postDetail->postRelatedData->first() : null;
                $postImage = $postRelatedData ? $postRelatedData->post_data : null;
                return [
                    'package_id' => $package->id,
                    'profile_id' => $package->profile_id,
                    'user_name' => $package->profileDetail->full_name,
                    'verified' => $package->profileDetail->verified,
                    'package_type' => $package->package_type,
                    'status' => $package->status,
                    'rejected_reason' => $package->rejected_reason,
                    'start_date' => $package->postDetail->start_date,
                    'end_date' => $package->postDetail->end_date,
                    'total' => $package->total,
                    'post_id' => $package->postDetail->id,
                    'post_image' => $postImage,
                    'caption' => $package->postDetail->caption,
                ];
            });

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'My boost post retrieved successfully',
                'data' => $filteredPackages,
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

    public function getAllBoostPost()
    {
        try {
            $userProfileId = auth()->user()->profile->id;

            $userInterests = ProfileInterest::where('Profile_id', $userProfileId)->pluck('interest_id')->toArray();

            $relatedPost = Post::where('boost_status', 'true')
                ->whereDate('end_date', '>', Carbon::now())->where(function ($query) use ($userInterests) {
                // foreach ($userInterests as $interest) {
                //     $profileInterests = explode(',', $interest);
                //     foreach ($profileInterests as $related) {
                //         $query->orWhereRaw("FIND_IN_SET($related, interest_id)");
                //     }
                // }
            })->get();

            $followingIds = Follows::select('followed_profile_id')->where('following_profile_id', $userProfileId)->pluck('followed_profile_id')->toArray();
            $followingPosts = Post::where('boost_status', 'true')
                ->whereDate('end_date', '>', Carbon::now())->whereIn('profile_id', $followingIds)->where(function ($query) use ($userInterests) {
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
            $perPage = 5;

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

            // $feed = $feeds->count();
            // if ($feed < 5) {
            //     $feeds = Post::inRandomOrder()->paginate(5);
            // }

            foreach ($feeds as $key => $post) {
                $loggedin_profile_id = auth()->user()->profile->id;

                $isFollowing = Follows::where('following_profile_id', $loggedin_profile_id)->where('followed_profile_id', $post->profile_id)->first();
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
                unset($post->created_at);
                unset($post->updated_at);
                unset($post->deleted_at);

                $post->share = 'https://ishtdevprod.netlify.app/post-share/' . $post->id;
                $isLiked = PostLike::select('like_flag')->where('profile_id', $loggedin_profile_id)->where('post_id', $post->id)->first();
                $post->isLiked = ($isLiked && $isLiked->like_flag == '1') ? 'true' : 'false';
                $profile = $this->processObject($post->profile);
                // if ($post->profile) {
                //     // $userPackage = UserPackage::where('profile_id', $post->profile->id)
                //     $userPackage = UserPackage::where('profile_id', $post->profile->id)->get();
                //     $package = $this->processObject($userPackage);
                //     $post->profile->package = $userPackage;
                //     // echo"hello";
                //     // echo "<pre>";
                //     // print_r($userPackage);
                // }
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
                            $user->verified =  null;
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

                $CommunityDetail = CommunityDetail::where('profile_id', $profile->id)->first();
                $post->profile->community_profile_id = $CommunityDetail->profile_id ?? null;
                $post->profile->name_of_community = $CommunityDetail->name_of_community ?? null;
                $post->profile->community_image = $CommunityDetail->community_image ?? null;

                $comments = $post->comments ?? null;
                foreach ($post->comments as $comment) {
                    $comment = $this->processObject($comment);
                    if ($comment->profile && $comment->profile->user) {
                        $commentUser = $this->processObject($comment->profile->user) ?? null;
                    }
                    if ($comment->profile && $comment->profile->user) {
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
            }
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Business boost posts retrieved successfully',
                'data' => $feeds,
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
    public function generateInvoice($boost_id)
    {
        $response = $this->getBoostRequestDetail($boost_id);
        if (!$response->isSuccessful()) {
            return $response;
        }
        $data = $response->getData();
        if(isset($data->data[0])){
            $boostRequest = $data->data[0];
            $pdf = PDF::loadView('invoices.invoice', compact('boostRequest'));
            return $pdf->download('invoice.pdf');
        }
}
}
