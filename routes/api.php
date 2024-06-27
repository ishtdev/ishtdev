<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\PanditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RewardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\HashtagMasterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReligionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PackageController;

Route::post('/otp', [AuthController::class, 'otp']);
Route::post('/sendOtp', [AuthController::class, 'sendOtp']);
Route::post('/otpVerification', [AuthController::class, 'otpVerification']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verifyUser', [AuthController::class, 'verifyUser']);
Route::post('/testVerifyUser', [AuthController::class, 'testVerifyUser']);
Route::post('/verifyAdmin', [AuthController::class, 'verifyAdmin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/uniqueUserName', [AuthController::class, 'uniqueUserName']);
Route::get('/showPostType', [PostController::class, 'showPostType']);
Route::get('/CommunityHistory/{communityId}', [CommunityController::class, 'showCommunityHistory']);
Route::get('/posts/{id}', [PostController::class, 'shows']);
Route::post('/updateFCM', [NotificationController::class, 'updateFCM']);
Route::post('/sendNotificationToOne', [NotificationController::class, 'sendNotificationToOne']);
Route::post('/sendNotificationWithPost', [NotificationController::class, 'sendNotificationWithPost']);

Route::middleware(['jwt.verify'])->group(function () {
	Route::get('/protected', function () {
        return response()->json(['message' => 'This route is protected by JWT authentication. You need a valid token to access it.']);
    });

	Route::post('profiles', [AuthController::class, 'update']);
    Route::post('userProfile', [AuthController::class, 'show']);
    Route::post('userProfile/{profileId}', [AuthController::class, 'showProfile']);
    Route::post('/user/follow/{userToFollow}',  [AuthController::class, 'follow']);
    Route::delete('/user/unfollow/{userToUnfollow}', [AuthController::class, 'unfollow']);
    Route::post('/user/getfollowing', [AuthController::class, 'showAllfollowed']);
    Route::post('/user/getfollow', [AuthController::class, 'showAllfollowing']);
    Route::post('/user/removefollower', [AuthController::class, 'removefollower']);
    Route::post('/uniqueFullName', [AuthController::class, 'uniqueFullName']);

	Route::post('/posts', [PostController::class, 'getPosts']);
	Route::get('/post/{id}', [PostController::class, 'show']);
	Route::get('/user-posts/{profileId}', [PostController::class, 'showUserPosts']);
	Route::post('/post/store', [PostController::class, 'store']);
    Route::post('/likePost', [PostController::class, 'likePost']);
    Route::post('/getallpostbyHashtag', [PostController::class, 'getallpostbyHashtag']);
    Route::delete('/delete-post/{postId}', [PostController::class, 'deletePost']);
    Route::delete('/delete-post-by-admin/{postId}', [PostController::class, 'deletePostByAdmin']);
    Route::post('/getPostCommnet', [PostController::class, 'getPostCommnet']);
    Route::post('/searchHashtag', [HashtagMasterController::class, 'searchHashtag']);
    Route::post('/post/comment', [CommentController::class, 'store']);
    Route::post('/likeComment', [CommentController::class, 'likeComment']);

    Route::post('/updateUserType', [UserController::class, 'updateUserType']);
    Route::post('/updateCommunityStatus', [UserController::class, 'updateCommunityStatus']);

    Route::post('/addupdateCommunity', [CommunityController::class, 'addupdateCommunity']);
    Route::post('/addupdateCommunityFacility', [CommunityController::class, 'addupdateCommunityFacility']);
    Route::delete('/deleteCommunityFacility/{facilityId}', [CommunityController::class, 'deleteCommunityFacility']);
    Route::get('/getUserCommunity/{profileId}', [CommunityController::class, 'getUserCommunity']);
    Route::get('/showCommunityDetails/{profileId}', [CommunityController::class, 'showCommunityDetails']);
    Route::post('/showAllCommunities', [CommunityController::class, 'showAllCommunities']);
    Route::get('/showAllCommunity', [CommunityController::class, 'showAllCommunity']);
    Route::post('/showFollowedCommunities', [CommunityController::class, 'showFollowedCommunities']);
    Route::post('/addCommunityHistory', [CommunityController::class, 'addCommunityHistory']);
    Route::get('/showCommunityHistory/{communityId}', [CommunityController::class, 'showCommunityHistory']);
    Route::post('/deleteCommunityHistory', [CommunityController::class, 'deleteCommunityHistory']);
    Route::post('/addupdateCommunityArti', [CommunityController::class, 'addupdateCommunityArti']);
    Route::get('/showCommunityArti/{communityId}', [CommunityController::class, 'showCommunityArti']);
    Route::post('/uniqueCommunityName', [CommunityController::class, 'uniqueCommunityName']);

    Route::get('/getAllCountries', [LocationController::class, 'getAllCountries']);
    Route::get('/getAllStates', [LocationController::class, 'getAllStates']);
    Route::get('/getAllCities/{state_id}', [LocationController::class, 'getAllCities']);
    Route::post('/searchCities', [LocationController::class, 'searchCities']);

    Route::get('/getAllGotras', [ReligionController::class, 'getAllGotras']);
    Route::get('/getAllReligion', [ReligionController::class, 'getAllReligion']);
    Route::get('/getAllKuldevta', [ReligionController::class, 'getAllKuldevta']);
    Route::get('/getAllVarna', [ReligionController::class, 'getAllVarna']);
    Route::get('/getAllIntrest', [ReligionController::class, 'getAllIntrest']);
    Route::get('/getUserInterest/{profile_id}', [ReligionController::class, 'getUserInterest']);
    Route::post('/addUpdateUserIntrest', [ReligionController::class, 'addUpdateUserIntrest']);

    Route::get('/recentCommunityList', [StoryController::class, 'recentCommunityList']);
    Route::post('/recentCommunityPost', [StoryController::class, 'recentCommunityPost']);
    Route::post('/uploadStory', [StoryController::class, 'uploadStory']);

    Route::post('/search-post', [SearchController::class, 'suggestionPost']);
    Route::get('/search-post/{postId}', [SearchController::class, 'searchPost']);

    Route::get('/showAllUsers', [UserController::class, 'showAllUsers']);
    Route::post('/searchCommunity', [SearchController::class, 'searchCommunity']);
    Route::post('/nearByCommunity', [SearchController::class, 'nearByCommunity']);
    Route::get('/searchUser/{name}', [SearchController::class, 'searchUser']);

    Route::post('/addPost', [NotificationController::class, 'addPost']);
    Route::post('/reportPost', [PostController::class, 'reportPost']);
    Route::post('/sendNotification', [NotificationController::class, 'sendNotification']);

    Route::post('/getBadgeImage', [RewardController::class, 'getBadgeImage']);
    Route::get('/getLordName', [RewardController::class, 'getLordName']);
    Route::get('/getUserBadge/{user_id}', [RewardController::class, 'getUserBadge']);
    Route::get('/getUserCheckIn/{user_id}', [RewardController::class, 'getUserCheckIn']);
    Route::delete('/deleteBadge/{badge_id}', [RewardController::class, 'deleteBadge']);

    Route::post('/addupdateWishlist', [WishlistController::class, 'addupdateWishlist']);
    Route::get('/allWishlist', [UserController::class, 'showAllWishlist']);
    Route::get('/wishlist-detail/{wishlist_id}', [WishlistController::class, 'wishlistDetail']);
    Route::get('/showUserWishlist/{profile_id}', [WishlistController::class, 'showUserWishlist']);
    Route::delete('/deleteWishlist/{wishlist_id}', [WishlistController::class, 'deleteWishlist']);

    //Get all Business

    Route::get('/showAllBusiness', [UserController::class, 'showAllBusinesslist']);
    
    Route::get('/green-tick-request', [UserController::class, 'greenTickRequest']);
    Route::post('/addupdate-package', [PackageController::class, 'createPackage']);
    Route::get('/get-all-package', [PackageController::class, 'getPackage']);
    Route::get('/get-package-detail/{package_id}', [PackageController::class, 'getPackageDetail']);
    Route::post('/boost-post', [PackageController::class, 'boostPost']);
    Route::post('/change-status', [PackageController::class, 'changeStatus']);
    Route::get('/get-user-transaction/{profile_id}', [PackageController::class, 'getUserTransaction']);
    Route::get('/get-boost-request', [PackageController::class, 'getBoostRequest']);
    Route::get('/boost-request-detail/{boost_id}', [PackageController::class, 'getBoostRequestDetail']);
    Route::get('/get-my-boost-post/{profile_id}', [PackageController::class, 'getMyBoostPost']);
    Route::get('/get-all-boost-post', [PackageController::class, 'getAllBoostPost']);
    
    Route::post('/generate-invoice/{boost_id}', [PackageController::class, 'generateInvoice']);

    // reported post
    Route::get('/get-all-reported-post', [UserController::class, 'getAllReportedPost']);
    Route::get('/get-reported-post/{id}', [UserController::class, 'getReportedPost']);



    Route::get('/get-amenities', [RewardController::class, 'getAmenities']);

    Route::get('/get-amenity/{amenity_id}', [RewardController::class, 'getAmenity']);
    // Route::get('/get-amenities', [RewardController::class, 'getAmenities']);
    Route::post('/addupdate-amenities', [RewardController::class, 'addupdateAmenities']);
    Route::delete('/delete-amenity/{amenity_id}', [RewardController::class, 'deleteAmenity']);
    Route::get('/restore-reported-post/{post_id}', [RewardController::class, 'restoreReportedPost']);
    
});

