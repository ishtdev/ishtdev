<?php

namespace App\Http\Controllers;

use App\Http\Controllers\NotificationController;
use App\Models\Address;
use App\Models\CommunityArti;
use App\Models\CommunityDetail;
use App\Models\Follows;
use App\Models\MobileOtps;
use App\Models\Post;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserDetails;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JWTAuth;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
use URL;
use Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/sendOtp",
     *     summary="Send Otp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"username", "mobile_number"},
     *             @OA\Property(property="username", type="string", example="John Doe", description="User name"),
     *             @OA\Property(property="mobile_number", type="numeric", format="number", example="9879546328", description="User's mobile number"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP Sent Successfully",
     *         @OA\JsonContent(
     *               @OA\Property(property="username", type="string", example="John Doe", description="User name"),
     *               @OA\Property(property="mobile_number", type="numeric", format="number", example="9879546328", description="User's mobile number"),
     *       )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input", description="Error message")
     *         )
     *     ),
     *   @OA\Response(
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
    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_number' => ['required', 'numeric', 'digits:10', 'regex:/^[6789]\d{9}$/', 'unique:users'],
                'full_name' => 'required|string|min:8|max:20',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();

                $response = [
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Mobile number and username should be unique.',
                    'data' => [],
                ];

                if ($errors->has('mobile_number')) {
                    $response['message'] = $errors->first('mobile_number');
                    return response()->json($response, 404);
                }

                if ($errors->has('full_name')) {
                    $response['message'] = $errors->first('full_name');
                    return response()->json($response, 404);
                }
            }

            $receiverNumber = "+91" . $request->mobile_number;

            if ($request->mobile_number == 9999999999) {
                $otp = 1111;
            } else {
                $otp = rand(1234, 9999);
            }

            $url = "https://api.enablex.io/sms/v1/messages/";
            $header = ["Authorization: Basic NjQ1MGU0MDZlNDYyZjllNDk3MDg3MTc0OnVoYTllN2VOdXB5ZHVlZTh1TWVtdTl5NXVCdXZlRGVWZURhcw==", "Content-Type: application/json"];
            $post_body = [
                "type" => "sms",
                "data_coding" => "auto",
                "campaign_id" => "21431457",
                "recipient" => [["to" => $receiverNumber, "var1" => "$otp"]],
                "from" => "ISTDEV",
                "template_id" => "741716857",
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);

            //$last4Digits = substr($request->mobile_number, -4);
            $randomDigits = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $newUsername = $request->full_name . $randomDigits;

            MobileOtps::create([
                'mobile_number' => $request->mobile_number,
                'username' => $newUsername,
                'verification_code' => $otp,
                'verified' => 0,
                'expire_time' => Carbon::now()->addMinutes(1),
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'OTP sent Successfully',
                //'data' => (object)[],
                'data' => [
                    'mobile-no' => $request->mobile_number,
                    'otp' => $otp,
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
     * PS-4 Level: Verify OTP for user authentication
     * @param \Illuminate\Http\Request $request - The request object containing the verification code
     * @return \Illuminate\Http\JsonResponse - JSON response with the status and user details
     * @OA\Post(
     *     path="/api/otpVerification",
     *     summary="Verify Otp",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"verification_code"},
     *             @OA\Property(property="verification_code", type="integer", format="number", example="2341", description="Enter Otp"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *         @OA\Property(property="verification_code", type="integer", format="number", example="2341", description="Enter Otp"),
     *               )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid otp", description="Error message")
     *         )
     *     ),
     *    @OA\Response(
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
    public function otpVerification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_code' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => "failure",
                    'message' => 'Validation Error',
                    'data' => [],
                ]);
            }
            $userOtp = MobileOtps::where('verification_code', $request->verification_code)->first();

            if (!$userOtp) {
                return response()->json([
                    'code' => 400,
                    'status' => "failure",
                    'message' => 'OTP is invalid',
                    'data' => (object) [],
                ]);
            } else {
                $userName = str_replace(' ', '', $userOtp->username);
                $userData = ['mobile_number' => $userOtp->mobile_number, 'username' => $userName];
            }

            $now = now();
            if ($request->verification_code !== '1111' && $now->isAfter($userOtp->expire_time)) {
                return response()->json([
                    'code' => 404,
                    'status' => "failure",
                    'message' => 'OTP is Expired',
                    'data' => (object) [],
                ]);
            }
            $findUser = User::where('mobile_number', $userOtp->mobile_number)->first();
            $jwtToken = null;
            $userId = '';
            $username = '';

            if ($findUser && !empty($userOtp)) {
                $username = $findUser->username;
                $Profiledata = Profile::where('user_id', $findUser->id)->first();
                $Profile = $Profiledata->id;
                $jwtToken = JWTAuth::fromUser($findUser);
            } else {
                $user = User::create($userData);
                if (!empty($userOtp->username)) {
                    $username = $userOtp->username;
                }
                $username = $userName;
                $userId = $user->id;
                $fullName = substr($userOtp->username, 0, -2);
                $Profiledata = Profile::create(['user_id' => $userId]);
                $UserDetails = UserDetails::create(['profile_id' => $Profiledata->id, 'full_name' => $fullName]);
                $addressDetails = Address::create(['profile_id' => $Profiledata->id]);
                $Profile = $Profiledata->id;
                $jwtToken = JWTAuth::fromUser($user);
            }

            if (!$jwtToken) {
                return response()->json([
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'JWT token not generated.',
                    'data' => [],
                ], 500);
            }

            $responseData = [
                'mobileNo' => $userOtp->mobile_number,
                'username' => $username,
                'userId' => $findUser ? $findUser->id : $userId,
                'profileId' => $Profile,
                'authorization' => $jwtToken,
                'isUserExisting' => $findUser ? true : false,
            ];

            $userOtp->update([
                'verification_code' => "",
                'expire_time' => now(),
                'verified' => 1,
            ]);

            return response()->json([
                'code' => 200,
                'status' => "success",
                'message' => 'OTP Verified Successfully',
                'data' => $responseData,
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

    /**
     * PS-4 Level: Logout User.
     *
     * Logs out the user by invalidating the JWT token.
     *
     * @param \Illuminate\Http\Request $request The request object.
     * @return \Illuminate\Http\JsonResponse Returns JSON response indicating the status of logout.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout User",
     *     tags={"Authentication"},
     *     description="Logs out the user by invalidating the JWT token.",
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Logout successful."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="status", type="string", example="Unauthorized"),
     *             @OA\Property(property="message", type="string", example="User is already logged out or token not found."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Failed to expire token",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=403),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to expire token."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="status", type="string", example="Internal Server Error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred during logout."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *         ),
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                //JWTAuth::setToken($token)->invalidate();
                //JWTAuth::setToken($token)->invalidate(true);

                if (JWTAuth::invalidate($token)) {
                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Logout successful.',
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 403,
                        'status' => 'error',
                        'message' => 'Failed to expire token.',
                    ], 401);
                }
            } else {
                return response()->json([
                    'code' => 401,
                    'status' => 'Unauthorized',
                    'message' => 'User is already logged out or token not found.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'Internal Server Error',
                'message' => 'An unexpected error occurred during logout.',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * PS-4 Level: Update user profile details
     *
     * @param \Illuminate\Http\Request $request - The HTTP request object containing updated profile data
     * @return \Illuminate\Http\JsonResponse - JSON response indicating the success or failure of the update
     * @OA\Post(
     *     path="/api/profiles",
     *     summary="Update Profile",
     *      tags={"Profile"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"profile_id", "full_name", "email", "dob", "religion", "varna", "gotra", "ishtdev", "kul_devta_devi", "bio", "poojatype_online", "poojatype_offline", "speciality_pooja", "pravara", "ved", "upved", "charanas", "mukha", "become_pandit", "street", "city", "state", "postal_code", "country"},
     *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="User profile ID"),
     *             @OA\Property(property="full_name", type="string", example="John Doe", description="User full name"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com", description="User email"),
     *             @OA\Property(property="dob", type="string", format="date", example="1990-01-01", description="User date of birth"),
     *             @OA\Property(property="religion", type="string", example="Hindu", description="User religion"),
     *             @OA\Property(property="varna", type="string", example="Brahmin", description="User varna"),
     *             @OA\Property(property="gotra", type="string", example="Vashishta", description="User gotra"),
     *             @OA\Property(property="ishtdev", type="string", example="Shiva", description="User ishtdev"),
     *             @OA\Property(property="kul_devta_devi", type="string", example="Durga", description="User kul devta/devi"),
     *             @OA\Property(property="bio", type="string", example="I am a spiritual person.", description="User biography"),
     *             @OA\Property(property="poojatype_online", type="string", example="Ganapati", description="User online pooja type"),
     *             @OA\Property(property="poojatype_offline", type="string", example="Lakshmi", description="User offline pooja type"),
     *             @OA\Property(property="speciality_pooja", type="string", example="Yajur Veda", description="User speciality pooja"),
     *             @OA\Property(property="pravara", type="string", example="Bhargava", description="User pravara"),
     *             @OA\Property(property="ved", type="string", example="Rigveda", description="User ved"),
     *             @OA\Property(property="upved", type="string", example="Sama Veda", description="User upved"),
     *             @OA\Property(property="charanas", type="string", example="Madhyama", description="User charanas"),
     *             @OA\Property(property="mukha", type="string", example="Dakshin", description="User mukha"),
     *             @OA\Property(property="become_pandit", type="string", example="approved", description="User become pandit status"),
     *             @OA\Property(property="street", type="string", example="123 Main St", description="User street address"),
     *             @OA\Property(property="city", type="string", example="Cityville", description="User city"),
     *             @OA\Property(property="state", type="string", example="Stateville", description="User state"),
     *             @OA\Property(property="postal_code", type="string", example="12345", description="User postal code"),
     *             @OA\Property(property="country", type="string", example="Countryland", description="User country"),
     *             @OA\Property(property="profile_picture", type="string", format="binary", description="User profile picture file"),
     *             @OA\Property(property="kyc_details_doc01", type="string", format="binary", description="User KYC details document 01 file"),
     *             @OA\Property(property="kyc_details_doc02", type="string", format="binary", description="User KYC details document 02 file"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile Updated Seuccessfully",
     *         @OA\JsonContent(
     *         @OA\Property(property="code", type="integer", example=200),
     *         @OA\Property(property="status", type="string", example="success"),
     *          @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *       )
     *     ),
     * @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Profile not found"),
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
    public function update(Request $request)
    {
        try {
            // echo "<pre>";
            // print_r($request->all());
            // die;
            $UserDetails = UserDetails::where('profile_id', $request->profile_id)->first();
            $addressDetails = Address::where('profile_id', $request->profile_id)->first();
            $ProfileDetails = Profile::where('id', $request->profile_id)->first();
            if (!$UserDetails) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                    'data' => (object) [],
                ], 404);
            }
            $device_key = '';
            $device_key = User::select('device_key')->where('id', $UserDetails['id'])->first();
            $validatedData = $request->all();
            $userDetailsData['verification_status'] = isset($validatedData['verification_status']) ? $validatedData['verification_status'] :
                ($request->has('verification_status') ? $request->input('verification_status') : $UserDetails['verification_status']);


            if ($request->has('verification_status')) {

                if ($validatedData['verification_status'] == 'approved' || $validatedData['verification_status'] == 'rejected') {
                    $chechInNotification = new NotificationController();
                    $to = $device_key['device_key'];
                    $notification = [
                        "title" => 'Ishtdev Notification',
                        "body" => 'Your Application is ' . $request->input('verification_status'),
                    ];
                    $data = [
                        "notication" => "true",
                        "source" => "CheckIn",
                    ];
                    $response = $chechInNotification->sendNotificationToOne($to, $notification, $data);
                }
            } else {
                // die("else");
                $userDetailsData['verification_status'] = 'pending';
            }

            // echo "<pre>";
            // print_r($userDetailsData);
            // die;
            // die("out");
            $userDetailsData['register_business_name'] = isset($validatedData['register_business_name']) || $request->has('register_business_name') ? $validatedData['register_business_name'] : $UserDetails['register_business_name'];
            $userDetailsData['verified'] = ($userDetailsData['verification_status'] == 'approved') ? 'true' : 'false';
            $userDetailsData['invalidate_reason'] = isset($validatedData['invalidate_reason']) || $request->has('invalidate_reason') ? $validatedData['invalidate_reason'] : $UserDetails['invalidate_reason'];
            $userDetailsData['full_name'] = isset($validatedData['full_name']) || $request->has('full_name') ? $validatedData['full_name'] : $UserDetails['full_name'];
            $userDetailsData['email'] = isset($validatedData['email']) || $request->has('email') ? $validatedData['email'] : $UserDetails['email'];
            $userDetailsData['religion'] = isset($validatedData['religion']) || $request->has('religion') ? $validatedData['religion'] : $UserDetails['religion'];
            $userDetailsData['varna'] = isset($validatedData['varna']) || $request->has('varna') ? $validatedData['varna'] : $UserDetails['varna'];
            $userDetailsData['gotra'] = isset($validatedData['gotra']) || $request->has('gotra') ? $validatedData['gotra'] : $UserDetails['gotra'];
            $userDetailsData['ishtdev'] = isset($validatedData['ishtdev']) || $request->has('ishtdev') ? $validatedData['ishtdev'] : $UserDetails['ishtdev'];
            $userDetailsData['dob'] = isset($validatedData['dob']) || $request->has('dob') ? $validatedData['dob'] : $UserDetails['dob'];
            $userDetailsData['kul_devta_devi'] = isset($validatedData['kul_devta_devi']) || $request->has('kul_devta_devi') ? $validatedData['kul_devta_devi'] : $UserDetails['kul_devta_devi'];
            $userDetailsData['bio'] = isset($validatedData['bio']) || $request->has('bio') ? $validatedData['bio'] : $UserDetails['bio'];
            $userDetailsData['poojatype_online'] = isset($validatedData['poojatype_online']) || $request->has('poojatype_online') ? $validatedData['poojatype_online'] : $UserDetails['poojatype_online'];
            $userDetailsData['poojatype_offline'] = isset($validatedData['poojatype_offline']) || $request->has('poojatype_offline') ? $validatedData['poojatype_offline'] : $UserDetails['poojatype_offline'];
            $userDetailsData['speciality_pooja'] = isset($validatedData['speciality_pooja']) || $request->has('speciality_pooja') ? $validatedData['speciality_pooja'] : $UserDetails['speciality_pooja'];
            $userDetailsData['pravara'] = isset($validatedData['pravara']) || $request->has('pravara') ? $validatedData['pravara'] : $UserDetails['pravara'];
            $userDetailsData['ved'] = isset($validatedData['ved']) || $request->has('ved') ? $validatedData['ved'] : $UserDetails['ved'];
            $userDetailsData['upved'] = isset($validatedData['upved']) || $request->has('upved') ? $validatedData['upved'] : $UserDetails['upved'];
            $userDetailsData['charanas'] = isset($validatedData['charanas']) || $request->has('charanas') ? $validatedData['charanas'] : $UserDetails['charanas'];
            $userDetailsData['mukha'] = isset($validatedData['mukha']) || $request->has('mukha') ? $validatedData['mukha'] : $UserDetails['mukha'];
            $userDetailsData['become_pandit'] = isset($validatedData['become_pandit']) || $request->has('become_pandit') ? $validatedData['become_pandit'] : $UserDetails['become_pandit'];
            $userDetailsData['make_profile_private'] = isset($validatedData['make_profile_private']) || $request->has('make_profile_private') ? $validatedData['make_profile_private'] : $UserDetails['make_profile_private'];

            $addressDetailsData['street'] = isset($validatedData['street']) || $request->has('street') ? $validatedData['street'] : $addressDetails['street'];
            $addressDetailsData['city'] = isset($validatedData['city']) || $request->has('city') ? $validatedData['city'] : $addressDetails['city'];
            $addressDetailsData['state'] = isset($validatedData['state']) || $request->has('state') ? $validatedData['state'] : $addressDetails['state'];
            $addressDetailsData['postal_code'] = isset($validatedData['postal_code']) || $request->has('postal_code') ? $validatedData['postal_code'] : $addressDetails['postal_code'];
            $addressDetailsData['country'] = isset($validatedData['country']) || $request->has('country') ? $validatedData['country'] : $addressDetails['country'];


            // convert to business

            $userDetailsData['is_business_profile'] = (
                isset($validatedData['business_verification_status']) && $request->has('business_verification_status') && $validatedData['business_verification_status'] === 'approved'
            ) ? 'true' : 'false';

            $userDetailsData['business_verification_status'] = 'pending';

            if (isset($validatedData['business_verification_status']) && $request->has('business_verification_status')) {
                $userDetailsData['business_verification_status'] = $validatedData['business_verification_status'];
            }

            if (
                isset($validatedData['business_verification_status']) &&
                $request->has('business_verification_status') &&
                $validatedData['business_verification_status'] === 'rejected'
            ) {
                $userDetailsData['business_invalidate_reason'] = $validatedData['business_invalidate_reason'];
            }

            if (isset($validatedData['business_verification_status']) && $request->has('business_verification_status')) {

                if ($validatedData['business_verification_status'] === 'approved' || $validatedData['business_verification_status'] === 'rejected') {
                    $chechInNotification = new NotificationController();
                    $to = $device_key['device_key'];
                    $notification = [
                        "title" => 'Ishtdev Notification',
                        "body" => 'Your Application is ' . $request->input('business_verification_status'),
                    ];
                    $notificationData = [
                        "notication" => "true",
                        "source" => "CheckIn",
                    ];
                    $response = $chechInNotification->sendNotificationToOne($to, $notification, $notificationData);
                }
            }

            $userDetailsData['business_city'] = $validatedData['business_city'] ?? $UserDetails['business_city'] ?? null;
            $userDetailsData['business_state'] = $validatedData['business_state'] ?? $UserDetails['business_state'] ?? null;
            $userDetailsData['business_pincode'] = $validatedData['business_pincode'] ?? $UserDetails['business_pincode'] ?? null;
            $userDetailsData['business_type'] = $validatedData['business_type'] ?? $UserDetails['business_type'] ?? null;
            $userDetailsData['business_name'] = $validatedData['business_name'] ?? $UserDetails['business_name'] ?? null;
            $userDetailsData['gst_number'] = $validatedData['gst_number'] ?? $UserDetails['gst_number'] ?? null;
            $userDetailsData['business_address'] = $validatedData['business_address'] ?? $UserDetails['business_address'] ?? null;

            if ($request->hasFile('business_doc')) {
                $file = $request->file('business_doc');
                $filename = $file->getClientOriginalName();
                $file->move(base_path() . '/public/business_doc/', $filename);
                $userDetailsData['business_doc'] = $validatedData['business_doc'] = 'business_doc/' . $filename;

                if (isset($userDetailsData['business_doc']) && $device_key) {
                    $chechInNotification = new NotificationController();
                    $to = $device_key['device_key'];
                    $notification = [
                        "title" => 'Ishtdev Notification',
                        "body" => 'Business document has been uploaded, please wait for approval',
                    ];
                    $data = [
                        "notication" => "true",
                        "source" => "CheckIn",
                    ];

                    $response = $chechInNotification->sendNotificationToOne($to, $notification, $data);
                    // echo"<pre>"; print_r($response); die;
                }

            }
            // end convert to business

            $userDetailsData['country'] = isset($validatedData['country']) || $request->has('country') ? $validatedData['country'] : $addressDetails['country'];

            if (isset($validatedData['doc_name'])) {
                $userDetailsData['doc_name'] = isset($validatedData['doc_name']) || $request->has('doc_name') ? $validatedData['doc_name'] : $UserDetails['doc_name'];

                if ($request->hasFile('doc_front')) {
                    $file = $request->file('doc_front');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/images/', $filename);
                    $userDetailsData['doc_front'] = $validatedData['doc_front'] = 'images/' . $filename;
                }

                if ($request->hasFile('doc_back')) {
                    $file = $request->file('doc_back');
                    $filename = $file->getClientOriginalName();
                    $file->move(base_path() . '/public/images/', $filename);
                    $userDetailsData['doc_back'] = $validatedData['doc_back'] = 'images/' . $filename;
                }

                $device_key = User::select('device_key')->where('id', $UserDetails['id'])->first();

                $chechInNotification = new NotificationController();
                $to = $device_key['device_key'];

                $notification = [
                    "title" => 'Documents is Uploded successfully',
                    "body" => 'Will get back to you after Verification',
                ];
                $data = [
                    "notication" => "true",
                    "source" => "CheckIn",
                ];
                $response = $chechInNotification->sendNotificationToOne($to, $notification, $data);
                //print_r($response);die();
            }

            if ($userDetailsData['become_pandit'] == "approved") {
                $profileDetailsData['user_type_id'] = '2';
                $ProfileDetails->update($profileDetailsData);
            }

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $filename = $file->getClientOriginalName();
                $file->move(base_path() . '/public/images/', $filename);
                $userDetailsData['profile_picture'] = $validatedData['profile_picture'] = 'images/' . $filename;
            }
            if ($request->hasFile('kyc_details_doc01')) {
                $file = $request->file('kyc_details_doc01');
                $filename = $file->getClientOriginalName();
                $file->move(base_path() . '/public/kycdocument/', $filename);
                $userDetailsData['kyc_details_doc01'] = $validatedData['kyc_details_doc01'] = 'kycdocument/' . $filename;
            }
            if ($request->hasFile('kyc_details_doc02')) {
                $file = $request->file('kyc_details_doc02');
                $filename = $file->getClientOriginalName();
                $file->move(base_path() . '/public/kycdocument/', $filename);
                $userDetailsData['kyc_details_doc02'] = $validatedData['kyc_details_doc02'] = 'kycdocument/' . $filename;
            }
            // echo"<pre>"; print_r($userDetailsData); die;
            $UserDetails->update($userDetailsData);
            $addressDetails->update($addressDetailsData);
            $address = $UserDetails->address;
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $this->processObject($UserDetails),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => (object) [],
            ], 500);
        }
    }

    /**
     * Display the details of a profile with the given profile_id.
     *
     * PS-4 Level: Show details of a profile with a given profile_id
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/userProfile",
     *     summary="User Profile",
     *     tags={"Profile"},
     *     @OA\Parameter(
     *         name="profile_id",
     *         in="query",
     *         required=true,
     *         description="Profile ID",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User data retrieved successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Profile not found"),
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
    public function show(Request $request)
    {
        try {
            $userTypeId = Profile::select('user_type_id')->where('id', $request->profile_id)->first();
            if (!$userTypeId) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                    'data' => [],
                ], 404);
            }
            if ($userTypeId['user_type_id'] == "1") {
                $userId = Profile::select('user_id')->where('id', $request->profile_id)->first();
                $UserDetails = User::where('id', $userId['user_id'])->get();
                $UserDetail = UserDetails::where('profile_id', $request->profile_id)->first();
                $userId = Profile::select('user_id')->where('id', $request->profile_id)->first();
                $isfollow = Follows::where('following_profile_id', auth()->user()->profile->id)
                    ->where('followed_profile_id', $request->profile_id)
                    ->count();
                $countFollow = Follows::where('following_profile_id', $request->profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $request->profile_id)->count();
                $postCount = Post::where('profile_id', $request->profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $request->profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $userData = $UserDetails->first();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User data',
                    'data' => array(
                        'userDetails' => $this->processObject($UserDetail),
                        'countFollowing' => $countFollow,
                        'countFollower' => $countFollowing,
                        'isfollow' => $isfollow ? "true" : "false",
                        'postCount' => $postCount,
                        'userTypename' => $userTypename,
                        'loggedIn' => auth()->user()->id == $userId['user_id'] ? "true" : "false",
                    ),
                ]);
            } elseif ($userTypeId['user_type_id'] == "2") {
                $UserDetails = UserDetails::where('profile_id', $request->profile_id)->first();
                $address = $this->processObject($UserDetails->address->address) ?? null;
                $getUserId = Profile::where('id', $request->profile_id)->first();
                $userId = $getUserId->user_id;
                $loginuserId = auth()->user()->id;
                $isfollow = Follows::where('following_profile_id', auth()->user()->profile->id)
                    ->where('followed_profile_id', $request->profile_id)
                    ->count();
                $countFollow = Follows::where('following_profile_id', $request->profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $request->profile_id)->count();
                $postCount = Post::where('profile_id', $request->profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $request->profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $communityUserId = Profile::select('user_id')->where('id', $request->profile_id)->first();
                $profileCount = Profile::where('user_id', $communityUserId['user_id'])->count();
                $communityCount = CommunityDetail::where('created_profile_id', $request->profile_id)->count();

                if (!$UserDetails) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Profile not found',
                        'data' => [],
                    ], 404);
                } else {
                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Profile data',
                        'data' => array(
                            'userDetails' => $this->processObject($UserDetails),
                            'countFollowing' => $countFollow,
                            'countFollower' => $countFollowing,
                            'isfollow' => $isfollow ? "true" : "false",
                            'postCount' => $postCount,
                            'userTypename' => $userTypename,
                            'loggedIn' => auth()->user()->id == $userId ? "true" : "false",
                            'communityCount' => $communityCount,
                        ),
                    ]);
                }
            } elseif ($userTypeId['user_type_id'] == "3") {
                $CommunityDetails = CommunityDetail::where('profile_id', $request->profile_id)->get();

                if (!$CommunityDetails) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Community not found',
                        'data' => [],
                    ], 404);
                }
                $getUserId = Profile::where('id', $request->profile_id)->first();
                $userId = $getUserId->user_id;
                $isfollow = Follows::where('following_profile_id', auth()->user()->profile->id)
                    ->where('followed_profile_id', $request->profile_id)
                    ->count();
                $countFollow = Follows::where('following_profile_id', $request->profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $request->profile_id)->count();
                $postCount = Post::where('profile_id', $request->profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $request->profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $communityData = $CommunityDetails->first();
                $liveArtiUrl = CommunityArti::where('community_detail_id', $communityData->id)->first()->live_arti_link ?? null;

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community data',
                    'data' => array(
                        'userDetails' => [
                            'id' => $communityData->id,
                            'profile_id' => $communityData->profile_id,
                            'name_of_community' => $communityData->name_of_community,
                            'status' => $communityData->status,
                            'community_image' => $communityData->community_image,
                            'community_image_background' => $communityData->community_image_background,
                            'short_description' => $communityData->short_description,
                            'location_of_community' => $communityData->location_of_community,
                            'schedual_visit' => $communityData->schedual_visit,
                            'community_lord_name' => $communityData->community_lord_name,
                            'qr_code' => $communityData->upload_qr,
                            'live_arti_url' => $liveArtiUrl,
                        ],
                        'latitude' => $communityData->latitude,
                        'longitude' => $communityData->longitude,
                        'countFollowing' => $countFollow,
                        'countFollower' => $countFollowing,
                        'isfollow' => $isfollow ? "true" : "false",
                        'postCount' => $postCount,
                        'userTypename' => $userTypename,
                        'loggedIn' => auth()->user()->id == $userId ? "true" : "false",
                        'visitCount' => 0,
                        'donationCount' => 0,
                    ),
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

    public function showProfile($profile_id)
    {
        try {
            $userTypeId = Profile::select('user_type_id')->where('id', $profile_id)->first();
            if (!$userTypeId) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Profile not found',
                    'data' => [],
                ], 404);
            }
            if ($userTypeId['user_type_id'] == "1") {
                $userId = Profile::select('user_id')->where('id', $profile_id)->first();
                $UserDetails = User::where('id', $userId['user_id'])->get();
                $UserDetail = UserDetails::where('profile_id', $profile_id)->first();
                $userId = Profile::select('user_id')->where('id', $profile_id)->first();
                $countFollow = Follows::where('following_profile_id', $profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $profile_id)->count();
                $postCount = Post::where('profile_id', $profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $userData = $UserDetails->first();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User data',
                    'data' => array(
                        'userDetails' => $this->processObject($UserDetail),
                        'countFollowing' => $countFollow,
                        'countFollower' => $countFollowing,
                        'postCount' => $postCount,
                        'userTypename' => $userTypename,
                    ),
                ]);
            } elseif ($userTypeId['user_type_id'] == "2") {
                $UserDetails = UserDetails::where('profile_id', $profile_id)->first();
                $address = $this->processObject($UserDetails->address->address) ?? null;
                $getUserId = Profile::where('id', $profile_id)->first();
                $userId = $getUserId->user_id;
                $countFollow = Follows::where('following_profile_id', $profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $profile_id)->count();
                $postCount = Post::where('profile_id', $profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $communityUserId = Profile::select('user_id')->where('id', $profile_id)->first();
                $profileCount = Profile::where('user_id', $communityUserId['user_id'])->count();
                $communityCount = CommunityDetail::where('created_profile_id', $profile_id)->count();
                if (!$UserDetails) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Profile not found',
                        'data' => [],
                    ], 404);
                } else {
                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Profile data',
                        'data' => array(
                            'userDetails' => $this->processObject($UserDetails),
                            'countFollowing' => $countFollow,
                            'countFollower' => $countFollowing,
                            'postCount' => $postCount,
                            'userTypename' => $userTypename,
                            'communityCount' => $communityCount,
                        ),
                    ]);
                }
            } elseif ($userTypeId['user_type_id'] == "3") {
                $CommunityDetails = CommunityDetail::where('profile_id', $profile_id)->get();
                if (!$CommunityDetails) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Community not found',
                        'data' => [],
                    ], 404);
                }
                $getUserId = Profile::where('id', $profile_id)->first();
                $userId = $getUserId->user_id;
                $countFollow = Follows::where('following_profile_id', $profile_id)->count();
                $countFollowing = Follows::where('followed_profile_id', $profile_id)->count();
                $postCount = Post::where('profile_id', $profile_id)->count();
                $getuserType = Profile::select('name')
                    ->join('user_type as ut', 'profile.user_type_id', '=', 'ut.id')
                    ->where('profile.id', $profile_id)
                    ->first();
                $userTypename = $getuserType->name;
                $communityData = $CommunityDetails->first();

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Community data',
                    'data' => array(
                        'userDetails' => [
                            'id' => $communityData->id,
                            'profile_id' => $communityData->profile_id,
                            'name_of_community' => $communityData->name_of_community,
                            'status' => $communityData->status,
                            'community_image' => $communityData->community_image,
                            'community_image_background' => $communityData->community_image_background,
                            'short_description' => $communityData->short_description,
                            'location_of_community' => $communityData->location_of_community,
                            'schedual_visit' => $communityData->schedual_visit,
                            'community_lord_name' => $communityData->community_lord_name,
                        ],
                        'countFollowing' => $countFollow,
                        'countFollower' => $countFollowing,
                        'postCount' => $postCount,
                        'userTypename' => $userTypename,
                        'visitCount' => 0,
                        'donationCount' => 0,
                    ),
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

    /**
     * Verify user with OTP.
     *
     * PS-4 Level: Verify user with OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/verifyUser",
     *     summary="Verify User",
     *      tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User details",
     *         @OA\JsonContent(
     *             required={"verification_code"},
     *             @OA\Property(property="mobile_number", type="integer", format="number", example="9876875567", description="User's mobile number"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP Sent Successfully",
     *         @OA\JsonContent(
     *            @OA\Property(property="mobile_number", type="integer", format="number", example="9876875567", description="User's mobile number"),
     *       )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mobile Number is invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Mobile Number is not valid"),
     *      )
     *    ),
     *  @OA\Response(
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
    public function verifyUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_number' => ['required', 'numeric', 'digits:10', 'regex:/^[6789]\d{9}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Mobile Number is invalid',
                    'data' => [],
                ], 404);
            }
            $receiverNumber = "+91" . $request->mobile_number;
            $findUser = User::where('mobile_number', $request->mobile_number)->first();
            if (!empty($findUser->mobile_number)) {

                if ($request->mobile_number == 9999999999) {
                    $otp = 1111;
                } else {
                    $otp = rand(1234, 9999);
                }
                $url = "https://api.enablex.io/sms/v1/messages/";
                $header = ["Authorization: Basic NjQ1MGU0MDZlNDYyZjllNDk3MDg3MTc0OnVoYTllN2VOdXB5ZHVlZTh1TWVtdTl5NXVCdXZlRGVWZURhcw==", "Content-Type: application/json"];

                $post_body = [
                    "type" => "sms",
                    "data_coding" => "auto",
                    "campaign_id" => "21431457",
                    "recipient" => [["to" => $receiverNumber, "var1" => "$otp"]],
                    "from" => "ISTDEV",
                    "template_id" => "741716857",
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_body));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);

                $last4Digits = substr($request->mobile_number, -4);

                $username = User::where('mobile_number', $request->mobile_number)->select('username')->first();

                MobileOtps::create([
                    'mobile_number' => $request->mobile_number,
                    'username' => $username['username'],
                    'verification_code' => $otp,
                    'verified' => 0,
                    'expire_time' => Carbon::now()->addMinutes(1),
                ]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    //'data' => (object)[],
                    'data' => array(
                        //"mobile-no" => $request->mobile_number,
                        "isUserExisting" => true,
                        //    "otp" => $otp
                    ),
                ]);
            } else {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User is not registered',
                    'data' => array(
                        "mobile-no" => $request->mobile_number,
                        "isUserExisting" => false,
                    ),
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

    public function testVerifyUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_number' => ['required', 'numeric', 'digits:10', 'regex:/^[6789]\d{9}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Mobile Number is invalid',
                    'data' => [],
                ], 404);
            }
            $receiverNumber = "+91" . $request->mobile_number;
            $findUser = User::where('mobile_number', $request->mobile_number)->first();
            if (!empty($findUser->mobile_number)) {
                if ($request->mobile_number == 9999999999) {
                    $otp = 1111;
                } else {
                    $otp = rand(1234, 9999);
                }
                $url = "https://api.enablex.io/sms/v1/messages/";
                $header = ["Authorization: Basic NjQ1MGU0MDZlNDYyZjllNDk3MDg3MTc0OnVoYTllN2VOdXB5ZHVlZTh1TWVtdTl5NXVCdXZlRGVWZURhcw==", "Content-Type: application/json"];

                $post_body = [
                    "type" => "sms",
                    "data_coding" => "auto",
                    "campaign_id" => "21431457",
                    "recipient" => [["to" => $receiverNumber, "var1" => "$otp"]],
                    "from" => "ISTDEV",
                    "template_id" => "741716857",
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_body));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);

                $last4Digits = substr($request->mobile_number, -4);
                $username = User::where('mobile_number', $request->mobile_number)->select('username')->first();

                MobileOtps::create([
                    'mobile_number' => $request->mobile_number,
                    'username' => $username['username'],
                    'verification_code' => $otp,
                    'verified' => 0,
                    'expire_time' => Carbon::now()->addMinutes(1),
                ]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'data' => array(
                        "mobile-no" => $request->mobile_number,
                        "isUserExisting" => true,
                        "otp" => $otp,
                    ),
                ]);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'User is not registered',
                    'data' => array(
                        "mobile-no" => $request->mobile_number,
                        "isUserExisting" => false,
                    ),
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

    /**
     * Verify user with OTP.
     *
     * PS-4 Level: Verify user with OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/verifyAdmin",
     *     summary="Verify Admin",
     *      tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin details",
     *         @OA\JsonContent(
     *             required={"verification_code"},
     *             @OA\Property(property="mobile_number", type="integer", format="number", example="9876875567", description="User's mobile number"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP Sent Successfully",
     *         @OA\JsonContent(
     *          @OA\Property(property="mobile_number", type="integer", format="number", example="9876875567", description="User's mobile number"),
     *       )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mobile Number is invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Mobile Number is not valid"),
     *      )
     *    ),
     *  @OA\Response(
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
    public function verifyAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_number' => ['required', 'numeric', 'digits:10', 'regex:/^[6789]\d{9}$/'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Mobile Number is invalid',
                    'data' => [],
                ], 404);
            }
            $receiverNumber = "+91" . $request->mobile_number;
            $findAdmin = User::where('mobile_number', $request->mobile_number)->where('role', 'admin')->first();
            if (!empty($findAdmin)) {

                $otp = rand(1234, 9999);

                $url = "https://api.enablex.io/sms/v1/messages/";
                $header = ["Authorization: Basic NjQ1MGU0MDZlNDYyZjllNDk3MDg3MTc0OnVoYTllN2VOdXB5ZHVlZTh1TWVtdTl5NXVCdXZlRGVWZURhcw==", "Content-Type: application/json"];

                $post_body = [
                    "type" => "sms",
                    "data_coding" => "auto",
                    "campaign_id" => "21431457",
                    "recipient" => [["to" => $receiverNumber, "var1" => "$otp"]],
                    "from" => "ISTDEV",
                    "template_id" => "741716857",
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_body));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);

                $last4Digits = substr($request->mobile_number, -4);
                $username = User::where('mobile_number', $request->mobile_number)->select('username')->first();

                MobileOtps::create([
                    'mobile_number' => $request->mobile_number,
                    'username' => $username['username'],
                    'verification_code' => $otp,
                    'verified' => 0,
                    'expire_time' => Carbon::now()->addMinutes(1),
                ]);

                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'data' => array(
                        "mobile-no" => $request->mobile_number,
                        "isUserExisting" => true,
                        "otp" => $otp,
                    ),
                ]);
            } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Admin is not registered',
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

    /**
     * Refresh the authentication token.
     *
     * PS-4 Level: Refresh the authentication token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        // PS-4 Level: Return a JSON response with a new token and the authenticated user
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ],
        ]);
    }

    /**
     * PS-4 Level: Follow a user or community
     *
     * @param int $userToFollow - The ID of the user or community to be followed
     * @return \Illuminate\Http\JsonResponse - JSON response with status, user details, and followed profile details
     * @OA\Post(
     *     path="/api/user/follow/{userToFollow}",
     *     summary="Follow User",
     *     tags={"Follower Management"},
     *     @OA\Parameter(
     *         name="userToFollow",
     *         in="path",
     *         required=true,
     *         description="User To Follow Profile Id",
     *         @OA\Schema(type="integer", format="int32", example=1),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User followed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="following_profile_details", type="object", description="Following user profile details"),
     *                 @OA\Property(property="followed_profile_details", type="object", description="Followed user profile details"),
     *             ),
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
    public function follow($profileID)
    {
        try {
            $profile_id = auth()->user()->profile->id;
            $following_user_details = User::where('id', auth()->user()->id)->first();
            $user_type_id = Profile::select('user_type_id')->where('id', $profileID)->first();
            if ($user_type_id['user_type_id'] == 3) {
                $followed_user_details = CommunityDetail::where('profile_id', $profileID)->first();
                if ($followed_user_details && isset($followed_user_details['status'])) {
                    $status = $followed_user_details['status'];
                    if ($status === 'pending' || $status === 'rejected' || $status === 'block') {
                        return response()->json([
                            'code' => 400,
                            'status' => 'error',
                            'message' => 'Community is not approved yet',
                            'data' => [],
                        ], 400);
                    } else {
                        //-------follow community channel start----------

                        $notificationController = new NotificationController();
                        $communityName = str_replace(' ', '', $followed_user_details['name_of_community']);
                        $deviceKey = User::select('device_key')->where('id', auth()->user()->id)->first();
                        $registrationTokens = [$deviceKey['device_key']];
                        $response = $notificationController->createChannel($communityName, $registrationTokens);

                        // echo $response;die();
                        //-------follow community channel end----------
                    }

                }
            } elseif ($user_type_id['user_type_id'] == 1 || $user_type_id['user_type_id'] == 2) {
                $userToFollowId = Profile::select('user_id')->where('id', $profileID)->first();
                $followed_user_details = User::where('id', $userToFollowId['user_id'])->first();
            }
            auth()->user()->profile->following()->syncWithoutDetaching($profileID);

            return response()->json([
                'status' => 'success',
                'user' => array(
                    "following_profile_details" => $this->processObject($following_user_details),
                    "followed_profile_details" => $this->processObject($followed_user_details)
                ),
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

    public function unfollow($profileID)
    {
        try {
            auth()->user()->profile->following()->detach($profileID);

            $following_user_details = User::where('id', auth()->user()->id)->first();

            $user_type_id = Profile::select('user_type_id')->where('id', $profileID)->first();

            if ($user_type_id['user_type_id'] == 3) {
                $unfollowed_user_details = CommunityDetail::where('profile_id', $profileID)->first();

                //-------create channel start----------
                $notificationController = new NotificationController();
                $communityName = str_replace(' ', '', $unfollowed_user_details['name_of_community']);
                $deviceKey = User::select('device_key')->where('id', auth()->user()->id)->first();
                $registrationTokens = [$deviceKey['device_key']];
                $response = $notificationController->removeDevice($communityName, $registrationTokens);
                //echo $response;die();

                //-------create channel end----------
            } elseif ($user_type_id['user_type_id'] == 1 || $user_type_id['user_type_id'] == 2) {
                $userToFollowId = Profile::select('user_id')->where('id', $profileID)->first();
                $unfollowed_user_details = User::where('id', $userToFollowId['user_id'])->first();
            }

            return response()->json([
                'status' => 'success',
                'user' => array(
                    "following_profile_details" => $this->processObject($following_user_details),
                    "unfollowed_profile_details" => $this->processObject($unfollowed_user_details)
                ),
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

    /**
     * PS-4 Level: Remove a follower
     *
     * @param \Illuminate\Http\Request $request - The HTTP request containing the profile_id_to_remove_follower
     * @return \Illuminate\Http\JsonResponse - JSON response with status, user details, and unfollowed user details
     * @OA\Post(
     *     path="/api/user/removefollower",
     *     summary="Remove Follower",
     *     tags={"Follower Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Remove Follower",
     *         @OA\JsonContent(
     *             required={"profile_id_to_remove_follower"},
     *             @OA\Property(property="profile_id_to_remove_follower", type="integer", format="int32", example=1, description="Profile ID of the follower to be removed"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User removed follower successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="following_user_details", type="object", description="Following user profile details"),
     *                 @OA\Property(property="unfollowed_user_details", type="object", description="Unfollowed user profile details"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Follower not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Follower not found"),
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
    public function removefollower(Request $request)
    { //to remove follower
        try {
            DB::table('follows')
                ->where('following_profile_id', $request->profile_id_to_remove_follower)
                ->where('followed_profile_id', auth()->user()->id)
                ->delete();

            $following_user_details = User::where('id', auth()->user()->id)->first();

            $following_profile_id = Follows::where('following_profile_id', $request->profile_id_to_remove_follower)
                ->where('followed_profile_id', auth()->user()->id)->get();

            if ($following_profile_id->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Follower not found',
                    'data' => [],
                ], 404);
            }

            $user_type_id = Profile::select('user_type_id')->where('id', $request->profile_id_to_remove_follower)->first();

            if ($user_type_id['user_type_id'] == 3) {
                $userToFollowId = Profile::select('user_id')->where('id', $request->profile_id_to_remove_follower)->first();
                $unfollowed_user_details = CommunityDetail::where('profile_id', $userToFollowId['user_id'])->get();
            } elseif ($user_type_id['user_type_id'] == 1 || $user_type_id['user_type_id'] == 2) {
                $userToFollowId = Profile::select('user_id')->where('id', $request->profile_id_to_remove_follower)->first();
                $unfollowed_user_details = User::where('id', $userToFollowId['user_id'])->first();
            }

            auth()->user()->followers()->detach($request->profile_id_to_remove_follower);

            return response()->json([
                'status' => 'success',
                'user' => array(
                    "following_user_details" => $this->processObject($following_user_details),
                    "unfollowed_user_details" => $this->processObject($unfollowed_user_details)
                ),
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

    /**
     * PS-4 Level: Show all profiles that the specified user is following
     *
     * @param \Illuminate\Http\Request $request - The HTTP request containing the profile_id
     * @return \Illuminate\Http\JsonResponse - JSON response with the list of followed profiles
     * @OA\Post(
     *     path="/api/user/showAllfollowed",
     *     summary="Show All Following User",
     *     tags={"Follower Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Show All Following User",
     *         @OA\JsonContent(
     *             required={"profile_id"},
     *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user to show followed"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Following User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="following_user_details", type="array", @OA\Items(
     *                     @OA\Property(property="username", type="string", example="John Doe", description="Username"),
     *                     @OA\Property(property="profile_picture", type="string", example="path/to/profile_picture", description="Profile picture URL"),
     *                     @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID"),
     *                     @OA\Property(property="followed_profile_id", type="integer", format="int32", example=2, description="Followed Profile ID"),
     *                 ), description="Array of followed user details"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Following not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Following not found"),
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
    public function showAllfollowed(Request $request)
    { //to show all following
        try {
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 200,
                    'status' => "failure",
                    'message' => 'Validation Error',
                    'data' => [],
                ]);
            }

            $followed = $followed_user = $followed_profile_details = array();

            $loginUserId = $request->profile_id;

            $follows = Follows::where('following_profile_id', $loginUserId)->get();

            if ($follows->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Following not found',
                    'data' => [],
                ], 404);
            }

            foreach ($follows as $follow) {
                $user_type_id = Profile::select('user_type_id')->where('id', $follow->followed_profile_id)->first();

                if ($user_type_id['user_type_id'] == 3) {
                    $followed_user_details = CommunityDetail::where('profile_id', $follow->followed_profile_id)->first();

                    $followed_user['username'] = $followed_user_details->name_of_community;
                    $followed_user['profile_picture'] = $followed_user_details->community_image;
                    $followed_user['profile_id'] = $followed_user_details->profile_id;
                } elseif ($user_type_id['user_type_id'] == 1 || $user_type_id['user_type_id'] == 2) {
                    $userToFollowId = Profile::select('user_id')->where('id', $follow->followed_profile_id)->first();
                    $followed_profile_details = UserDetails::where('id', $userToFollowId['user_id'])->first();

                    $followed_user['username'] = $followed_profile_details->full_name;
                    $followed_user['profile_picture'] = $followed_profile_details->profile_picture;
                    $followed_user['profile_id'] = $followed_profile_details->profile_id;
                }

                $followed_user['followed_profile_id'] = $follow->followed_profile_id;
                $followed[] = $followed_user;
            }

            return response()->json([
                'status' => 'success',
                'user' => array("following_user_details" => $followed),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => [], // Include any relevant data here
            ], 500);
        }
    }

    /**
     * PS-4 Level: Show all profiles that are following the specified user
     *
     * @param \Illuminate\Http\Request $request - The HTTP request containing the profile_id
     * @return \Illuminate\Http\JsonResponse - JSON response with the list of followers
     * @OA\Post(
     *     path="/api/user/showAllfollowing",
     *     summary="Show All Followers",
     *     tags={"Follower Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Show All Followers",
     *         @OA\JsonContent(
     *             required={"profile_id"},
     *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user to show followed"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Followers retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="following_user_details", type="array", @OA\Items(
     *                     @OA\Property(property="username", type="string", example="John Doe", description="Username"),
     *                     @OA\Property(property="profile_picture", type="string", example="path/to/profile_picture", description="Profile picture URL"),
     *                     @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID"),
     *                     @OA\Property(property="following_profile_id", type="integer", format="int32", example=2, description="Following Profile ID"),
     *                 ), description="Array of following user details"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Follower not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Follower not found"),
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
    public function showAllfollowing(Request $request)
    { //to show all follower
        try {
            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'profile_id' => 'required',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json([
                    'code' => 200,
                    'status' => "failure",
                    'message' => 'Validation Error',
                    'data' => [],
                ]);
            }

            $followed = $followed_user = $followed_profile_details = array();
            $loginPofileId = $request->profile_id;

            // Fetch all records where the specified user is being followed by others
            $follows = Follows::where('followed_profile_id', $loginPofileId)->get();

            // Check if there are no records of followers
            if ($follows->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Follower not found',
                    'data' => [],
                ], 404);
            }

            // Loop through the follower profiles and fetch details based on user_type_id
            foreach ($follows as $follow) {
                // Get the user_type_id of the follower profile
                $user_type_id = Profile::select('user_type_id')->where('id', $follow->following_profile_id)->first();

                if ($user_type_id['user_type_id'] == 3) {
                    // Fetch details for user_type_id 3 (Community Details)
                    $followed_user_details = CommunityDetail::where('profile_id', $follow->following_profile_id)->first();

                    $followed_user['username'] = $followed_user_details->name_of_community;
                    $followed_user['profile_picture'] = $followed_user_details->community_image;
                    $followed_user['profile_id'] = $followed_user_details->profile_id;
                } elseif ($user_type_id['user_type_id'] == 1 || $user_type_id['user_type_id'] == 2) {
                    // Fetch details for user_type_id 1 or 2 (User Details)
                    $userToFollowId = Profile::select('user_id')->where('id', $follow->following_profile_id)->first();
                    $followed_profile_details = UserDetails::where('id', $userToFollowId['user_id'])->first();

                    $followed_user['username'] = $followed_profile_details->full_name;
                    $followed_user['profile_picture'] = $followed_profile_details->profile_picture;
                    $followed_user['profile_id'] = $followed_profile_details->profile_id;
                }

                $followed_user['following_profile_id'] = $follow->following_profile_id;
                $followed[] = $followed_user;
            }

            return response()->json([
                'status' => 'success',
                'user' => array("following_user_details" => $followed),
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

    /**
     * PS-4 Level: Check full name availability.
     *
     * @param string $name The name of the user to check availability for.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/uniqueFullName",
     *     summary="Check Full Name Availability",
     *     tags={"Profile"},
     *     description="Check if a full name is available or already exists.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Full Name of the user",
     *         @OA\Schema(type="string", example="John Doe"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response indicating whether the full name is available.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Full name available"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Full name already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Full name already exists"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
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
    public function uniqueFullName(Request $request)
    {
        try {
            $name = $request->name;
            $existingRecord = UserDetails::where("full_name", $searchValue)->first();

            if (isset($existingRecord)) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',

                    'message' => 'Full name already exist',
                    'data' => array(
                        "isNameExisting" => true,
                    ),
                ]);
            }
            return response()->json([
                'code' => 200,
                'status' => 'success',

                'message' => 'Full name available',
                'data' => array(
                    "isNameExisting" => false,

                ),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => (object) [],
            ], 500);
        }
    }

    /**
     * PS-4 Level: Process and sanitize an object by removing specified fields
     *
     * @param mixed $object - The object or array to be processed
     * @return mixed - The processed object with specified fields removed
     */
    private function processObject($object)
    {
        $fieldsToRemove = ['password', 'created_at', 'created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];

        if (is_object($object) || is_array($object)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }

            foreach ($object as $key => $value) {
                $object->{$key} = $this->processObject($value);
            }

            if (isset($object->address)) {
                unset($object->address->created_at);
                unset($object->address->updated_at);
                unset($object->address->deleted_at);
            }
        }

        return $object;
    }

    /**
     * PS-4 Level: Check user name availability.
     *
     * @param string $name The name of the user to check availability for.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *     path="/api/uniqueUserName",
     *     summary="Check Username Availability",
     *     tags={"Authentication"},
     *     description="Check if a username is available or already exists.",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Username of the user",
     *         @OA\Schema(type="string", example="John Doe"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response indicating whether the username is available.",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Username available"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Username already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Username already exists"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An unexpected error occurred.",
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
    public function uniqueUserName(Request $request)
    {
        try {
            $searchValue = strtolower($request->name);
            $usernames = User::pluck('username')->toArray();
            $existingRecord = null;
            $isUserNameExisting = false;

            foreach ($usernames as $username) {
                $usernameWithoutLastFourDigits = substr($username, 0, -4);
                $usernameWithoutLastFourDigitsLowerCase = strtolower($usernameWithoutLastFourDigits);
                if ($usernameWithoutLastFourDigitsLowerCase === $searchValue) {
                    $isUserNameExisting = true;
                    break;
                }
            }

            if ($isUserNameExisting) {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Username already exists',
                    'data' => [
                        "isUserNameExisting" => true,
                    ],
                ]);
            } else {
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Username available',
                    'data' => [
                        "isUserNameExisting" => false,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'errors' => [$e->getMessage()],
                'data' => (object) [],
            ], 500);
        }
    }

}
