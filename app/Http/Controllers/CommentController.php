<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
//use App\Models\CommentLike;
use App\Models\Profile;
use JWTAuth, Validator,  Hash, URL, Helper, File, Stripe, Session, Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class CommentController extends Controller
{

    /**
     * PS-4 Level: remove fields.
     *
     * @param \Illuminate\Http\Request $request The request instance.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function processObject($object)
    {
        $fieldsToRemove = ['password', 'created_at', 'created_at', 'deleted_at', 'updated_at', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];

        if (!empty($object)) {
            foreach ($fieldsToRemove as $field) {
                unset($object->{$field});
            }
        }
        return $object;
    }

    /**
     * PS-4 Level: Store Comment.
     *
     * @param \Illuminate\Http\Request $request The request instance.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
    *     path="/api/post/comment",
    *     summary="Save Comment",
    *     tags={"Post"},
    *     @OA\RequestBody(
    *         required=true,
    *         description="Save Comment",
    *         @OA\JsonContent(
    *             required={"comment", "profile_id", "post_id"},
    *             @OA\Property(property="comment", type="string", example="This is a comment", description="Comment text"),
    *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user creating the comment"),
    *             @OA\Property(property="post_id", type="integer", format="int32", example=1, description="ID of the post to which the comment is attached"),
    *             @OA\Property(property="parent_comment_id", type="integer", format="int32", example=2, description="ID of the parent comment if this is a reply"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Comment added successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="user", type="object",
    *                 @OA\Property(property="postCommentData", type="object", description="Details of the created comment",
    *                     @OA\Property(property="comment", type="string", example="This is a comment", description="Comment text"),
    *                     @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user creating the comment"),
    *                     @OA\Property(property="post_id", type="integer", format="int32", example=1, description="ID of the post to which the comment is attached"),
    *                     @OA\Property(property="parent_comment_id", type="integer", format="int32", example=2, description="ID of the parent comment if this is a reply"),
    *                 ),
    *                 @OA\Property(property="postCommentCount", type="integer", example=3, description="Total count of comments for the post"),
    *                 @OA\Property(property="commentsReplies", type="array", @OA\Items(
    *                     @OA\Property(property="comment", type="string", example="Reply to the comment", description="Reply text"),
    *                     @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user creating the reply"),
    *                     @OA\Property(property="post_id", type="integer", format="int32", example=1, description="ID of the post to which the reply is attached"),
    *                     @OA\Property(property="parent_comment_id", type="integer", format="int32", example=2, description="ID of the parent comment"),
    *                 ), description="Array of replies to the comment if applicable"),
    *             ),
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="User, Post, or Parent Comment not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="User, Post, or Parent Comment not found"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Validation Error",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=400),
    *             @OA\Property(property="status", type="string", example="failure"),
    *             @OA\Property(property="message", type="string", example="Validation failed"),
    *             @OA\Property(property="errors", type="object", description="Validation errors"),
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
    public function store(Request $request){ // to store comment
        try {
            $validator = Validator::make($request->all(), [
                'comment' => 'required',
                'profile_id' => 'required',
                'post_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'failure',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 400);
            }
            $findUser = Profile::where('id', $request->profile_id)->first();
            $findPost = Post::where('id', $request->post_id)->first();
            if(empty($findUser->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User not found',
                    'data' => array(),
                ]);
            }else if(empty($findPost->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Post not found',
                    'data' => array(),
                ]);
            }else if(empty($findPost->id) && empty($findUser->id)){
                return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'User and Post not found',
                    'data' => array(),
                ]);
            }
            $validatedData = $request->only(['comment', 'profile_id', 'post_id','parent_comment_id']);

            $postComment = new Comment();
            $postComment->comment = $validatedData['comment'];
            $postComment->profile_id = $validatedData['profile_id'];
            $postComment->post_id = $validatedData['post_id'];
            $postComment->parent_comment_id = isset($validatedData['parent_comment_id'])?$validatedData['parent_comment_id']:0 ;
            $postComment->save();
            $profile = $postComment->profile->user ?? null;
            $post = $postComment->post->posts ?? null;
            $postCommentCount = Comment::where('post_id', $validatedData['post_id'])->count();
            if(isset($validatedData['parent_comment_id'])){
            $commentsReplies = Comment::with('replies')->where('parent_comment_id',$validatedData['parent_comment_id'])->orderBy('id', 'DESC')->get();
            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Comment Created Successfully',
                'data' => array(
                    "postCommentData" => $postComment,
                    "postCommentCount" => $postCommentCount,
                    'commentsReplies' => $commentsReplies
                ),
            ];}else{$response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Comment Created Successfully',
                'data' => array(
                    "postCommentData" => $postComment,
                    "postCommentCount" => $postCommentCount,
                ),
            ];}

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
     * PS-4 Level: Like Comment.
     *
     * @param \Illuminate\Http\Request $request The request instance.
     *
     * @return \Illuminate\Http\JsonResponse
      * @OA\Post(
    *     path="/api/likeComment",
    *     summary="Like Comment",
    *     tags={"Post"},
    *     @OA\RequestBody(
    *         required=true,
    *         description="Like Comment",
    *         @OA\JsonContent(
    *             required={"profile_id", "comment_id"},
    *             @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user liking the comment"),
    *             @OA\Property(property="comment_id", type="integer", format="int32", example=1, description="ID of the comment to be liked"),
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Comment liked successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="string", example="success"),
    *             @OA\Property(property="user", type="object",
    *                 @OA\Property(property="commentlikeCount", type="integer", example=3, description="Total count of likes for the comment"),
    *                 @OA\Property(property="likeData", type="object", description="Details of the like operation",
    *                     @OA\Property(property="profile_id", type="integer", format="int32", example=1, description="Profile ID of the user liking the comment"),
    *                     @OA\Property(property="like_flag", type="integer", format="int32", example=1, description="Like status (1 for liked, 0 for unliked)"),
    *                     @OA\Property(property="comment_id", type="integer", format="int32", example=2, description="ID of the liked comment"),
    *                 ),
    *             ),
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Profile or Comment not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="code", type="integer", example=404),
    *             @OA\Property(property="status", type="string", example="error"),
    *             @OA\Property(property="message", type="string", example="Profile or Comment not found"),
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
    
    public function likeComment(Request $request)
	{
        try {
            $validator = Validator::make($request->all(), [
                'profile_id'   => ['required', 'numeric'],
                'comment_id' => ['required', 'numeric'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'code' => 404,
                    'status' => 'failure',
                    'message' => 'Validation Error',
                    'data' => [],
                ], 404);
            }
            $findProfile = Profile::where('id', $request->profile_id)->first();
            $findComment = Comment::where('id', $request->comment_id)->first();
            if(empty($findProfile->id)){
                return response()->json([
                    'code' => 404,
                    'status' => 'success',
                    'message' => 'Profile not found',
                    'data' => array(),
            ]);
           }else if(empty($findComment->id)){
                return response()->json([
                    'code' => 404,
                    'status' => 'success',
                    'message' => 'Comment not found',
                    'data' => array(),
                ]);
           }
           $validatedData = $request->only(['profile_id', 'like_flag', 'comment_id']);
           $findPreviousLikes  = CommentLike::where('comment_id', $validatedData['comment_id'])->where('profile_id', $validatedData['profile_id'])->first();
           $findPreviousLike = $this->processObject($findPreviousLikes);
           if(!empty($findPreviousLike)){
                if($findPreviousLike->like_flag == 0){
                    $sendData = array("like_flag" => 1);
                }else{
                    $sendData = array("like_flag" => 0);
                }

                $findPreviousLike->update($sendData);

           }else{
            $commentLike = new CommentLike();
            $commentLike->profile_id = $validatedData['profile_id'];
            $commentLike->like_flag = 1;
            $commentLike->comment_id = $validatedData['comment_id'];
            $commentLike->save();
           }

           $commentLikeCount = CommentLike::where('comment_id', $validatedData['comment_id'])->where('like_flag', 1)->count();
           
           return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Comment Liked Successfully',
            'data' => array(
                "commentlikeCount" => $commentLikeCount,
                "likeData" => $findPreviousLike ? $findPreviousLike :  $commentLike,
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
*/

}
