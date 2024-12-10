<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lord;
use App\Models\Badge;
use App\Models\CommunityBadge;
use App\Models\Amenities;
use Illuminate\Support\Facades\Log;
use App\Models\UserCheckIn;
use App\Models\PostReport;
use App\Models\PostLike;
use App\Models\PostHashtag;
use App\Models\PostData;
use App\Models\Comment;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use App\Models\CommunityDetail;
use JWTAuth, Validator, Hash, URL, Helper, File, Stripe, Session, Exception;
use Illuminate\Validation\Rule;
// header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class RewardController extends Controller
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

    private function processCollection($collection)
    {
        foreach ($collection as $item) {
            $this->processObject($item);
        }
        return $collection;
    }


    public function getLordName(Request $request)
    {
        try {
            $lordName = Lord::select('id', 'lord_name')->get();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All Lord retrived successfully',
                'data' => $lordName,
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

    public function getLordBadge()
    {
        try {

            $lordBadge = Badge::with('lord')->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'LordBadge retrived successfully',
                'data' => $lordBadge,
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

    public function getLordBadgeDetail($id)
    {
        try {

            $lordBadge = Badge::with('lord')->where('id', $id)->first();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'LordBadge retrived successfully',
                'data' => $lordBadge,
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

    public function addLordBadge(Request $request)
    {
        try {

            // echo"<pre>";print_r($request->all()); die;
            // header('Access-Control-Allow-Origin: *');
            // 'contact_email' => 'required|email|unique:branches,contact_email',

            $validator = Validator::make($request->all(), [
                'lord_name' =>  'required|string|max:255|unique:lord,lord_name',
                'silver_type' =>  'required|string|max:255',
                'gold_type' =>  'required|string|max:255',
                'bronze_type' =>  'required|string|max:255',
                'silver_image' => 'required|mimes:jpg,jpeg,png,gif,svg',
                'gold_image' => 'required|mimes:jpg,jpeg,png,gif,svg',
                'bronze_image' => 'required|mimes:jpg,jpeg,png,gif,svg',
            ]);


            // Check if the validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => $validator->errors(),
                ], 404);
            }


            $lord = new Lord();
            $lord->lord_name = $request->lord_name;
            $lord->save();

            $lordId = $lord->id;


            // Handle creating a new amenity
            $badge1 = new Badge();
            $badge1->lord_id = $lordId;

            $badge1->type = $request->silver_type;

            if ($request->hasFile('silver_image')) {
                $image1 = $request->file('silver_image');
                $imageName1 = time() . '_' . uniqid() . '.' . $image1->getClientOriginalExtension();
                $image1->move(base_path('public/interestImage/'), $imageName1);
                $badge1->image = 'interestImage/' . $imageName1;
            }
            $badge1->save();

            $badge2 = new Badge();
            $badge2->lord_id = $lordId;

            $badge2->type = $request->gold_type;

            if ($request->hasFile('gold_image')) {
                $image2 = $request->file('gold_image');
                $imageName2 = time() . '_' . uniqid() . '.' . $image2->getClientOriginalExtension();
                $image2->move(base_path('public/interestImage/'), $imageName2);
                $badge2->image = 'interestImage/' . $imageName2;
            }
            $badge2->save();

            $badge3 = new Badge();
            $badge3->lord_id = $lordId;

            $badge3->type = $request->bronze_type;

            if ($request->hasFile('bronze_image')) {
                $image3 = $request->file('bronze_image');
                $imageName3 = time() . '_' . uniqid() . '.' . $image3->getClientOriginalExtension();
                $image3->move(base_path('public/interestImage/'), $imageName3);
                $badge3->image = 'interestImage/' . $imageName3;
            }


            $badge3->save();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'LordBadge added successfully',
                // 'data' => $badge,
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

    public function updateLordBadge(Request $request)
    {
        // header('Access-Control-Allow-Origin: *');
        DB::beginTransaction();
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:badge,id', // Validate that the badge ID exists
                // 'lord_name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,svg,webp,ico,heic,heif', // Optional image validation
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => $validator->errors(),
                ], 404);
            }

            $validatedData = $request->all();

            // Find the badge
            $lordBadge = Badge::find($validatedData['id']);
            if (!$lordBadge) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Badge not found',
                ], 404);
            }

            // Load associated lord
            $lordBadge->load('lord');
            if (!$lordBadge->lord) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'No associated lord found for this badge',
                ], 404);
            }

            // Update lord data
            // $lord = $lordBadge->lord;
            // $lord->lord_name = $validatedData['lord_name'];
            // $lord->save();

            // Handle optional image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(base_path('public/interestImage/'), $imageName);

                // Delete old image file if it exists
                if ($lordBadge->image && file_exists(base_path('public/interestImage/' . $lordBadge->image))) {
                    unlink(base_path('public/' . $lordBadge->image));
                }
                // Update image path in the database
                $lordBadge->image = 'interestImage/' . $imageName;
            }

            // Save badge
            $lordBadge->save();

            DB::commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Lord and Badge data updated successfully',
                'data' => $lordBadge->toArray(),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 500);
        }
    }


    public function getBadgeImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lord_id' => ['required'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => [],
                ], 404);
            }

            $badgeImage = Badge::select('id', 'image', 'type')->where('lord_id', $request->lord_id)->get();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Badge Image retrived successfully',
                'data' => $badgeImage,
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

    public function getUserBadge($user_id)
    {
        try {
            $userBadges = UserCheckIn::where('user_id', $user_id)
                ->whereNotNull('community_badge_id')
                ->with([
                    'badge_details' => function ($query) {
                        $query->select('id', 'badge_id', 'title', 'community_id', 'check_in_count');
                    },
                    'badge_details.badge_type' => function ($query) {
                        $query->select('id', 'lord_id', 'type', 'image');
                    },
                    'badge_details.badge_type.lord' => function ($query) {
                        $query->select('id', 'lord_name');
                    },
                ])
                ->get()
                ->toArray();

            if (count($userBadges) > 0) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'All Badges retrieved successfully',
                    'data' => $userBadges,
                ]);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No Badges found',
                    'data' => [],
                ]);
            }
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

    public function getUserCheckIn($user_id)
    {
        try {
            $userBadge = UserCheckIn::where('user_id', $user_id)
                ->with([
                    'community' => function ($query) {
                        $query->select('id', 'name_of_community', 'community_image');
                    }
                ])->orderByDesc('created_at')->paginate(10);

            // Convert the dates from UTC to IST (India Standard Time) for each record
            $userBadge->getCollection()->transform(function ($record) {
                // Convert 'created_at' from UTC to IST (India Standard Time)
                $record->created_at = Carbon::parse($record->created_at)
                    ->timezone('Asia/Kolkata')   // Convert to IST
                    ->format('Y-m-d H:i:s');      // Format the result

                // Convert 'updated_at' from UTC to IST (India Standard Time)
                $record->updated_at = Carbon::parse($record->updated_at)
                    ->timezone('Asia/Kolkata')   // Convert to IST
                    ->format('Y-m-d H:i:s');      // Format the result

                return $record;
            });


            if (count($userBadge) > 0) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Check-ins found',
                    'data' => $userBadge,
                ]);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No check-ins found',
                    'data' => [],
                ]);
            }
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

    public function deleteBadge($badge_id)
    {
        try {
            $userBadge = CommunityBadge::find($badge_id);

            if (!$userBadge) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Badge not found',
                    'data' => (object) [],
                ], 404);
            }
            $userBadge->deleted_at = Carbon::now();
            $userBadge->save();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Badge deleted successfully',
                'data' => $userBadge,
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

    public function getAmenities()
    {
        try {
            $amenities = Amenities::whereNull('deleted_at')->get();

            if ($amenities->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Amenities not found',
                    'data' => (object) [],
                ], 404);
            }

            $processedAmenities = $this->processCollection($amenities);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'All Amenities Retrieved Successfully',
                'data' => $processedAmenities,
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

    public function getAmenity($amenity_id)
    {
        try {
            $amenity = Amenities::whereNull('deleted_at')->where('id', $amenity_id)->first();

            if (!$amenity) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Amenities not found',
                    'data' => (object) [],
                ], 404);
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Amenities Retrieved Successfully',
                'data' => $amenity,
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

    public function addupdateAmenities(Request $request)
    {
        // print_r($request->all());die;


        try {

            // Check if updating an existing amenity
            if ($request->id) {

                $validator = Validator::make($request->all(), [
                    'amenity_name' => [
                        'required',
                        Rule::unique('amenities', 'amenity_name')->ignore($request->id)->whereNull('deleted_at'),
                    ],
                    'icon' => 'mimes:svg,png',
                ]);

                // Check if the validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'failure',
                        'message' => 'Validation Error',
                        'data' => $validator->errors(),
                    ], 404);
                }

                $amenity = Amenities::find($request->id);
                if (!$amenity) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'failure',
                        'message' => 'Amenity not found',
                    ], 404);
                }

                $amenity->amenity_name = $request->amenity_name;

                if ($request->hasFile('icon')) {
                    // Handle file upload for new icon
                    $image = $request->file('icon');
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(base_path('public/amenities/'), $imageName);

                    // Delete old icon file if it exists
                    if ($amenity->icon && file_exists(base_path('public/' . $amenity->icon))) {
                        unlink(base_path('public/' . $amenity->icon));
                    }

                    // Update icon path in the database
                    $amenity->icon = 'amenities/' . $imageName;
                }

                $amenity->save();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Amenity Updated Successfully',
                    'data' => $amenity,
                ]);
            } else {
                // Create a validator instance using the facade
                $validator = Validator::make($request->all(), [
                    'amenity_name' => [
                        'required',
                        Rule::unique('amenities', 'amenity_name')->ignore($request->id)->whereNull('deleted_at'),
                    ],
                    'icon' => 'required|mimes:svg,png',
                ]);

                // Check if the validation fails
                if ($validator->fails()) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'failure',
                        'message' => 'Validation Error',
                        'data' => $validator->errors(),
                    ], 404);
                }

                // Handle creating a new amenity
                $amenity = new Amenities();
                $amenity->amenity_name = $request->amenity_name;

                if ($request->hasFile('icon')) {
                    $image = $request->file('icon');
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(base_path('public/amenities/'), $imageName);
                    $amenity->icon = 'amenities/' . $imageName;
                }

                $amenity->save();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Amenity Created Successfully',
                    'data' => $amenity,
                ]);
            }
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


    public function deleteAmenity($amenity_id)
    {
        try {
            $amenity = Amenities::find($amenity_id);

            if (!$amenity) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Amenity not found',
                    'data' => (object) [],
                ], 404);
            }
            $amenity->deleted_at = Carbon::now();
            $amenity->save();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Amenity deleted successfully',
                'data' => $amenity,
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

    public function restoreReportedPost($post_id)
    {
        try {
            $postReportCount = PostReport::where('post_id', $post_id)->count();
            $post = Post::where('id', $post_id)->withTrashed()->first();

            $post_comment = Comment::where('post_id', $post_id)->first();
            $post_data = PostData::where('post_id', $post_id)->first();
            $post_hashtag = PostHashtag::where('post_id', $post_id)->first();
            $post_like = PostLike::where('post_id', $post_id)->first();

            PostReport::where('post_id', $post_id)->delete();

            if ($postReportCount >= 3 && $post && $post->deleted_at !== null) {
                $post->deleted_at = null;
                $post->save();
            } elseif ($postReportCount < 3 && $post && $post->deleted_by == 'admin') {
                $post->deleted_at = null;
                $post->save();
            }
            if ($post_comment && $post_comment->deleted_at !== null) {
                $post->deleted_at = null;
                $post->save();
            }
            if ($post_data && $post_data->deleted_at !== null) {
                $post->deleted_at = null;
                $post->save();
            }
            if ($post_hashtag && $post_hashtag->deleted_at !== null) {
                $post->deleted_at = null;
                $post->save();
            }
            if ($post_like && $post_like->deleted_at !== null) {
                $post->deleted_at = null;
                $post->save();
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Post Restored successfully',
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
}
