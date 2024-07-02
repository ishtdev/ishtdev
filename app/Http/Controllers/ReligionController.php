<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gotra;
use App\Models\Religion;
use App\Models\Profile;
use App\Models\Kuldevta;
use App\Models\Interest;
use App\Models\Varna;
use App\Models\User;
use App\Models\ProfileInterest;
use Validator, Exception;

class ReligionController extends Controller
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
     * PS-4 Level: Get All Gotras.
     *
     * Retrieves all gotras of a specific state.
     *
     * @param \Illuminate\Http\Request $request The request object containing required parameters.
     * @return \Illuminate\Http\JsonResponse Returns JSON response with gotras data.
     *
     * @OA\get(
     *     path="/api/getAllGotras",
     *     summary="Retrieve All Gotras",
     *    tags={"Profile"},
     *     @OA\Response(
     *         response=200,
     *         description="Gotras retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All gotras retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the gotra"),
     *                 @OA\Property(property="name", type="string", example="Gotra Name", description="Name of the gotra"),
     *             )),
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
    public function getAllGotras(){
        try{
            $gotras = Gotra::orderBy('name')->get();
            $gotraData = [];
            foreach ($gotras as $gotra) {
                $gotraData[] = [
                    'id' => $gotra->id,
                    'name' => $gotra->name,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All Gotras retrieved successfully',
                            'data' => $gotraData, 
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

    /**
     * PS-4 Level: Get All Religion.
     *
     * Retrieves all religions.
     *
     * @param \Illuminate\Http\Request $request The request object containing required parameters.
     * @return \Illuminate\Http\JsonResponse Returns JSON response with religions data.
     *
     * @OA\get(
     *     path="/api/getAllReligion",
     *     summary="Retrieve All Religion",
     *         tags={"Profile"},
     *     @OA\Response(
     *         response=200,
     *         description="Religion retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All religions retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the religion"),
     *                 @OA\Property(property="name", type="string", example="Religion Name", description="Name of the religion"),
     *             )),
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
    public function getAllReligion(){
        try{
            $religions = Religion::orderBy('name')->get();
            $religionData = [];
            foreach ($religions as $religion) {
                $religionData[] = [
                    'id' => $religion->id,
                    'name' => $religion->name,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All Religion retrieved successfully',
                            'data' => $religionData, 
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

     /**
     * PS-4 Level: Retrieve All Kuldevta
     *
     * Retrieves all Kuldevtas.
     *
     * @return \Illuminate\Http\JsonResponse Returns JSON response with Kuldevtas data.
     *
     * @OA\Get(
     *     path="/api/getAllKuldevta",
     *     summary="Retrieve All Kuldevta",
     *     tags={"Profile"},
     *     security={{ "bearer_token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Kuldevtas retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All Kuldevtas retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the Kuldevta"),
     *                     @OA\Property(property="name", type="string", example="Kuldevta Name", description="Name of the Kuldevta"),
     *                 ),
     *             ),
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
     * )
     */
    public function getAllKuldevta(){
        try{
            $kuldevi = Kuldevta::orderBy('name')->get();
            $kuldevtaData = [];
            foreach ($kuldevi as $kuldevta) {
                $kuldevtaData[] = [
                    'id' => $kuldevta->id,
                    'name' => $kuldevta->name,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All kuldevta retrieved successfully',
                            'data' => $kuldevtaData, 
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

    /**
     * PS-4 Level: Retrieve All Varna
     *
     * Retrieves all Varnas.
     *
     * @return \Illuminate\Http\JsonResponse Returns JSON response with Varnas data.
     *
     * @OA\Get(
     *     path="/api/getAllVarna",
     *     summary="Retrieve All Varna",
     *     tags={"Profile"},
     *     security={{ "bearer_token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Varnas retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All Varnas retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the Varna"),
     *                     @OA\Property(property="name", type="string", example="Varna Name", description="Name of the Varna"),
     *                 ),
     *             ),
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
     * )
     */
    public function getAllVarna(){
        try{
            $varnas = Varna::orderBy('name')->get();
            $varnaData = [];
            foreach ($varnas as $varna) {
                $varnaData[] = [
                    'id' => $varna->id,
                    'name' => $varna->name,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All varna retrieved successfully',
                            'data' => $varnaData, 
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

    /**
     * PS-4 Level: Retrieve All Intrest
     *
     * Retrieves all Intrest.
     *
     * @return \Illuminate\Http\JsonResponse Returns JSON response with Intrest data.
     *
     * @OA\Get(
     *     path="/api/getAllIntrest",
     *     summary="Retrieve All Interest",
     *     tags={"Profile"},
     *     security={{ "bearer_token": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Interests retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All interests retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the Interest"),
     *                     @OA\Property(property="name", type="string", example="Interest Name", description="Name of the Interest"),
     *                     @OA\Property(property="image", type="string", example="Interest Image", description="Image of the Interest"),
     *                 ),
     *             ),
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
     * )
     */
    public function getAllIntrest(){
        try{
            $interests = Interest::get();
            $interestData = [];
            foreach ($interests as $interest) {
                $interestData[] = [
                    'id' => $interest->id,
                    'name' => $interest->name_of_interest,
                    'image' => $interest->image_of_interest,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All interest retrieved successfully',
                            'data' => $interestData, 
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

    /**
     * PS-4 Level: Get User Intereser
     *
     * Get User Intereser
     *
     * @return \Illuminate\Http\JsonResponse Returns JSON response with Intrest data.
     *
     * @OA\Get(
     *     path="/api/getUserInterest/{profile_id}",
     *     summary="Retrieve All Interest",
     *     tags={"Profile"},
     *     security={{ "bearer_token": {} }},
     *     @OA\Parameter(
     *         name="profile_id",
     *         in="path",
     *         required=true,
     *         description="ID of the profile",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Interests retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All interest retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the Interest"),
     *                     @OA\Property(property="name", type="string", example="Interest Name", description="Name of the Interest"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User interest does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User interest does not exist"),
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
     * )
     */
    public function getUserInterest($profile_id){
        try{
            $interests = ProfileInterest::where('profile_id', $profile_id)->orderBy('name_of_interest')->get();
            if($interests->isEmpty()){
                return response()->json([
                            'code' => 404,
                            'status' => 'error',
                            'message' => 'User interest does not exist',
                            'data' => [],
                        ], 200);
            }
            $interestData = [];
            foreach ($interests as $interest) {
                $names = explode(',', $interest->name_of_interest);

                foreach ($names as $name) {
                    $interestData[] = [
                        
                        'name' => trim($name),
                    ];
                }
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All interest retrieved successfully',
                            'data' => $interestData, 
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

    /**
     * PS-4 Level: Add or Update User Interest
     *
     * Adds or updates user interest.
     *
     * @return \Illuminate\Http\JsonResponse Returns JSON response with the result of adding or updating user interest.
     *
     * @OA\Post(
     *     path="/api/addUpdateUserIntrest",
     *     summary="Add or Update User Interest",
     *     tags={"Profile"},
     *     security={{ "bearer_token": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User profile ID, interest IDs, and interest names",
     *         @OA\JsonContent(
     *             required={"profile_id", "interest_ids", "interest_names"},
     *             @OA\Property(property="profile_id", type="integer", example=1, description="ID of the user profile"),
     *             @OA\Property(property="interest_ids", type="string", example="1,2,3", description="Comma-separated list of interest IDs"),
     *             @OA\Property(property="interest_names", type="string", example="Rama, Krishna, Ganesha", description="Comma-separated list of interest names"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Interests added or updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Interests added or updated successfully"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="object", description="Validation errors"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User Profile does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User Profile does not exist"),
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
     * )
     */
    public function addUpdateUserIntrest(Request $request){
         try {
            $validator = Validator::make($request->all(), [
                'profile_id' => ['required', 'numeric'],
                'interest_ids' => ['required', 'string'], 
                'interest_names' => ['required', 'string'], 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                    'data' => [],
                ], 400);
            }
            $profile = Profile::where('id', $request->profile_id)->first();
            if(!$profile){
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'User Profile does not exist',
                    'data' => [],
                ], 400);
            }
            $interestExist = ProfileInterest::where('profile_id', $request->profile_id)->exists();

            if($interestExist) {
                $profileInterest = ProfileInterest::where('profile_id', $request->profile_id)->first();
                $profileInterest->update([
                    'interest_id' => $request->interest_ids,
                    'name_of_interest' => $request->interest_names
                ]);
                unset($profileInterest->id);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Interests updated successfully',
                    'data' => $this->processObject($profileInterest),
                ]);
            } else {
                $profileInterest = new ProfileInterest();
                $profileInterest->profile_id = $request->profile_id;
                $profileInterest->interest_id = $request->interest_ids; 
                $profileInterest->name_of_interest = $request->interest_names;
                $profileInterest->save();
                unset($profileInterest->id); 

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Interests added successfully',
                    'data' => $this->processObject($profileInterest),
                ]);
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
}
