<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator,  Exception;
use Carbon\Carbon;
use  App\Models\UserDetails;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function addupdateWishlist(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'title' => 'nullable',
                'date' => 'nullable|date_format:Y-m-d',
                'planning_with' => 'nullable',
                'num_of_male' => 'nullable|integer',
                'num_of_female' => 'nullable|integer',
                'num_of_child' => 'nullable|integer',
                'profile_id' => 'required|integer',
            ]);

            if($validator->fails()){
                    return response()->json([
                    "code" => 400,
                    "status" => "success",
                    "message" => "Validation Failed",
                    "data" => $validator->errors(),
                ], 400);
            }
            if($request->has("wishlist_id")){
                $existingWishlist = Wishlist::find($request->wishlist_id);
                $existingWishlist->title = $request->title;
                $existingWishlist->date = $request->date;
                $existingWishlist->profile_id = $request->profile_id;
                $existingWishlist->planning_with = $request->planning_with;
                $existingWishlist->total_member = ($request->num_of_male ?? 0) + ($request->num_of_female ?? 0) + ($request->num_of_child ?? 0);
                $existingWishlist->num_of_male = $request->num_of_male;
                $existingWishlist->num_of_female = $request->num_of_female;
                $existingWishlist->num_of_child = $request->num_of_child;
                $existingWishlist->save();
                return response()->json([
                    "code" => 200,
                    "status" => "success",
                    "message" => "Wishlist Updated Successfully",
                    "data" => []
                ], 200);
            }else{
                $wishlist = new Wishlist();
                $wishlist->title = $request->title;
                $wishlist->date = $request->date;
                $wishlist->profile_id = $request->profile_id;
                $wishlist->planning_with = $request->planning_with;
                $wishlist->total_member = ($request->num_of_male ?? 0) + ($request->num_of_female ?? 0) + ($request->num_of_child ?? 0);
                $wishlist->num_of_male = $request->num_of_male;
                $wishlist->num_of_female = $request->num_of_female;
                $wishlist->num_of_child = $request->num_of_child;
                $wishlist->save();
                return response()->json([
                    "code" => 200,
                    "status" => "success",
                    "message" => "Wishlist Created Successfully",
                    "data" => []
                ], 200);
            }

        }catch(\Exeption $e){
            return response()->json([
                "code" => 500,
                "status" => "error",
                "message" => "An enexpected error occurred.",
                "error" => [$e->get_message()],
                "data" => []
            ], 500);
        }
    }

    public function wishlistDetail($wishlistId){
        try{
            $wishlistExist = Wishlist::find($wishlistId);
            if (!$wishlistExist) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    "message" => "Wishlist Does not exist",
                    'data' => (object)[],
                ], 404);
            }

            $wishlist = Wishlist::where('id', $wishlistId)
                        ->with([
                            'user_detail' => function ($query) {
                                $query->select('id', 'profile_id', 'full_name', 'profile_picture', 'email'); 
                            },
                        ])
                        ->first();

                return response()->json([
                    "code" => 200,
                    "status" => "success",
                    "message" => "Wishlist Details Retrieved Successfully",
                    "data" => $wishlist,
                ], 200);

        }catch(\Exeption $e){
            return response()->json([
                "code" => 500,
                "status" => "error",
                "message" => "An enexpected error occurred.",
                "error" => [$e->get_message()],
                "data" => []
            ], 500);
        }
    }

    public function showUserWishlist($profileId){
        try{
            $user = UserDetails::where('profile_id', $profileId)->first();
            if(!$user){
                return response()->json([
                    "code" => 200,
                    "status" => "success",
                    "message" => "User Does not exist",
                    "data" => (object)[],
                ], 200);
            }
            $wishlist = Wishlist::where('profile_id', $profileId)
                    ->whereDate('date', '>=', now()->toDateString())
                    ->paginate(10);
            if($wishlist->isEmpty()) {
                return response()->json([
                    "code" => 200,
                    "status" => "success",
                    "message" => "User Wishlist Does not exist",
                    "data" => (object)[],
                ], 200);
            }

            return response()->json([
                "code" => 200,
                "status" => "success",
                "message" => "User Wishlist Retrieved Successfully",
                "data" => $wishlist,
            ], 200);

        }catch(\Exeption $e){
            return response()->json([
                "code" => 500,
                "status" => "error",
                "message" => "An enexpected error occurred.",
                "error" => [$e->get_message()],
                "data" => []
            ], 500);
        }
    }

    public function deleteWishlist($wishlistId){
        try{
            $wishlist = Wishlist::find($wishlistId);
            if (!$wishlist) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    "message" => "Wishlist Does not exist",
                    'data' => (object)[],
                ], 404);
            }
            $wishlist->deleted_at = Carbon::now();
            $wishlist->save();
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Wishlist deleted successfully',
                'data' => $wishlist,
            ]);

        }catch(\Exeption $e){
            return response()->json([
                "code" => 500,
                "status" => "error",
                "message" => "An enexpected error occurred.",
                "error" => [$e->get_message()],
                "data" => []
            ], 500);
        }
    }
}
