<?php

namespace App\Http\Controllers;

use Validator, Exception;
use App\Models\Countries;
use App\Models\States;
use App\Models\Cities;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getAllCountries(){
        try{
            $countries = Countries::get();
            $countrieData = [];
            foreach($countries as $country){
                $countrieData[] = [
                'id' => $country->id,
                'name' => $country->name,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All Countries retrieved successfully',
                            'data' => $countrieData, 
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
     * PS-4 Level: Get All States.
     *
     * Retrieves all states or states of a specific country.
     *
     * @param \Illuminate\Http\Request $request The request object containing optional parameters.
     * @return \Illuminate\Http\JsonResponse Returns JSON response with states data.
     *
     * @OA\GET(
     *     path="/api/getAllStates",
     *     summary="Retrieve All States",
     *     tags={"Profile"},
     *     @OA\Response(
     *         response=200,
     *         description="States retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All states retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the state"),
     *                 @OA\Property(property="name", type="string", example="State Name", description="Name of the state"),
     *                 @OA\Property(property="countryId", type="integer", format="int32", example=101, description="ID of the country the state belongs to"),
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
    public function getAllStates(){
        try{
            // $validator = Validator::make($request->all(), [
            //     'country_id' => 'nullable',
            // ]);
            // $data = $request->all();
            // if ($request->country_id){   
            //     $states = States::where('country_id', $data['country_id'])->get();
            // }else {
            //     $states = States::get();
            // }
            $states = States::where('country_id', 101)->orderBy('name')->get();
            $stateData = [];
            foreach ($states as $state) {
                $stateData[] = [
                    'id' => $state->id,
                    'name' => $state->name,
                    'countryId' => $state->country_id,
                ];
            }
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All States retrieved successfully',
                            'data' => $stateData, 
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
     * PS-4 Level: Get All Cities.
     *
     * Retrieves all cities of a specific state.
     *
     * @param \Illuminate\Http\Request $request The request object containing required parameters.
     * @return \Illuminate\Http\JsonResponse Returns JSON response with cities data.
     *
     * @OA\GET(
     *     path="/api/getAllCities/{state_id}",
     *     summary="Retrieve All Cities",
     *     tags={"Profile"},
     *     description="Retrieves all cities of a specific state.",
     *     @OA\Parameter(
     *         name="state_id",
     *         in="path",
     *         required=true,
     *         description="ID of state",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *   @OA\Response(
     *         response=200,
     *         description="Cities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All states retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the state"),
     *                 @OA\Property(property="name", type="string", example="State Name", description="Name of the state"),
     *                 @OA\Property(property="countryId", type="integer", format="int32", example=101, description="ID of the country the state belongs to"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="City not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="City not found"),
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
    public function getAllCities($state_id){
        try{
            $cities = Cities::where('state_id', $state_id)->orderBy('name')->get();
            if (!$cities) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'City not found',
                    'data' => [],
                ], 404);
            }
            $cityData = [];
            foreach ($cities as $city) {
                $cityData[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'stateId' => $city->state_id,
                    'countryId' => $city->country_id,
                ];
            } 
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All Cities retrieved successfully',
                            'data' => $cityData, 
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
     * PS-4 Level: Search Cities.
     *
     * Retrieves cities matching a search query for a specific state.
     *
     * @param \Illuminate\Http\Request $request The request object containing the search query and state ID.
     * @return \Illuminate\Http\JsonResponse Returns JSON response with cities data.
     *
     * @OA\Post(
     *     path="/api/searchCities",
     *     summary="Search Cities",
     *     tags={"Profile"},
     *     description="Retrieves cities matching a search query for a specific state.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="JSON object containing the search query and state ID.",
     *         @OA\JsonContent(
     *             required={"name", "state_id"},
     *             @OA\Property(property="name", type="string", example="City Name", description="Search query for city name"),
     *             @OA\Property(property="state_id", type="integer", format="int32", example=1, description="ID of the state")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cities retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="All cities retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", format="int32", example=1, description="ID of the city"),
     *                 @OA\Property(property="name", type="string", example="City Name", description="Name of the city"),
     *                 @OA\Property(property="stateId", type="integer", format="int32", example=1, description="ID of the state the city belongs to"),
     *                 @OA\Property(property="countryId", type="integer", format="int32", example=101, description="ID of the country the city belongs to"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"state_id": {"The state_id field is required."}})
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
    public function searchCities(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'state_id' => 'required',
            ]);
            if($validator->fails()){
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],400);
            }
            $searchValue = $request->name; 
            $tag = preg_replace("/[^a-zA-Z]/", '', $searchValue);
            $existingRecord = Cities::where('name','LIKE','%'.$tag.'%')->where('state_id',$request->state_id)->get();
            $cityData = [];
            foreach ($existingRecord as $city) {
                $cityData[] = [
                    'id' => $city->id,
                    'name' => $city->name,
                    'stateId' => $city->state_id,
                    'countryId' => $city->country_id,
                ];
            } 
            return response()->json([
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'All Cities retrieved successfully',
                            'data' => $cityData, 
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
}
