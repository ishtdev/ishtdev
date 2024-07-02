<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostType;
use App\Models\PostLike;
use App\Models\PostData;
use App\Models\HashtagMaster;
//use App\Image;
use Image;
use App\Models\Notification;
use Illuminate\Support\Str;
use JWTAuth, Validator,  Hash, URL, Helper ,File, Stripe, Session, Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class HashtagMasterController extends Controller
{

    /**
     * PS-4 Level: Process Object.
     *
     * @param mixed $object The object to be processed.
     *
     * @return mixed The processed object with specified fields removed.
     */
    private function processObject($object)	{ //remove some fields form output 
        $fieldsToRemove = ['password', 'created_at', 'created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
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
    *     path="/api/searchHashtag",
    *     summary="Search Hashtag",
    *     tags={"Post"},
    *     @OA\RequestBody(
    *         required=true,
    *         description="Search Hashtag",
    *         @OA\JsonContent(
    *             required={"name"},
    *             @OA\Property(property="name", type="string", example="#awesome", description="Search value for the hashtag"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Hashtag found successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="data", type="array", @OA\Items(
    *                  @OA\Property(property="name", type="string", example="#awesome", description="Search value for the hashtag"),
    *   ), description="Array of found hashtags"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="No hashtag found",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="No hashtag found"),
    *         )
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
    public function searchHashtag(Request $request){  
        try {
            $searchValue = $request->name; 
            if (strpos($searchValue, '%') !== false || strpos($searchValue, '_')) {
            return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'All hashtag retrived successfully',
                    'data' => [],
                ]);
            }
            // $tag = preg_replace("#[^a-zA-Z0-9_]#", '', $searchValue);
            $existingRecord = HashtagMaster::whereRaw("name LIKE '%{$searchValue}%'")->get();
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'All hashtag retrived successfully',
                    'data' => $this->processObject($existingRecord),
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
