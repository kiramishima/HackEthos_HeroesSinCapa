<?php

namespace App\Http\Controllers;

use App\User;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Create a new post.
     *
     * @return void 
     */
    public function add_Post (Request $request) {
        try {
            $this->validate($request, [
                'body'    => 'required',
            ]);
            session_start();
            session_name('torchwood');
            $body = $request->input('body');
            // Agrega el post
            $post = new Post;
            $post->users_id = $_SESSION['Id_Usuario'];
            $post->body = $body;
            if($request->has('image')){
                $post->image = $request->input('image');
            }
            if($request->has('tags')){
                $post->tags = $request->input('tags');
            }
            $post->save();
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => "Post creado de manera exitosa"
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 401,
                'message'    => $e->getMessage()
            ], 401);
        }  catch (PDOException $e) {
            return response()->json([
                'status' => 401,
                'message'    => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message'    => $e->getMessage()
            ], 500);
        }
    }

    // Registro de nuevos usuarios
    public function list_posts (Request $request) {
        try {
            $results = DB::select("SELECT posts.*, users.name, users.lastname, users.alias, (SELECT COUNT(*) FROM post_likes WHERE post_likes.post_id = posts.id) AS likes FROM posts
            LEFT JOIN users ON users.id = posts.users_id
            ORDER BY posts.created_at DESC, likes DESC");

            return response()->json([
                'status' => 200,
                'success' => true,
                'data' => $results
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message'    => $ex->getMessage()
            ], 400);
        } catch (QueryException $ex) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message'    => $ex->getMessage()
            ], 500);
        } catch (PDOException $ex) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message'    => $ex->getMessage()
            ], 500);
            //return response()->json($response, 500);
        } catch (FatalErrorException $e) {
            return response()->json([
                'status' => 400,
                'success' => false,
                'message'    => $e->getMessage()
            ], 500);
        }
    }

    // Registro de nuevos usuarios
    public function test () {
        try {
            echo phpinfo();
        } catch (Exception $ex) {
            
        }
    }
}
