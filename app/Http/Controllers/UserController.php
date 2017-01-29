<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void 
     */
    public function login (Request $request) {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required'
            ]);

            $email = $request->input('email');
            $password = hash('sha256', $request->input('password'));
            // Valida que el usuario exista
            $user_login = User::whereRaw('email = ? or alias = ?', array($email, $email))->first();
            $response = [];

            if ($user_login) {
                // Valida que el usuario y contraseña sean correctos
                $login = User::whereRaw('(email = ? or alias = ?) and password = ?', array($email, $email, $password))->first();
                if ($login) {
                    // Auth::login($user_login, true);
                    session_start();
                    session_name('torchwood');
                    $_SESSION['Id_Usuario'] = $user_login->id;
                    return response()->json([
                        'status' => 200,
                        'success' => true,
                        'message' => `Bienvenido #{$user_login->name}`,
                        'Id_Usuario' => $user_login->id
                    ], 200);
                } else {
                    throw new \Exception("La contrasena es incorrecta, intente de nuevo.");
                }
            } else {
                throw new \Exception("Usuario no encontrado, intente de nuevo");
            }
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
    public function profile() {
        try {
            session_start();
            session_name('torchwood');
            $results = DB::select("SELECT users.id, users.name, users.lastname, users.email, (SELECT COUNT(*) FROM posts WHERE users_id = users.id) AS pub_posts, (SELECT COUNT(*) FROM post_likes 
            LEFT JOIN posts ON posts.id = post_likes.post_id WHERE posts.users_id = users.id) AS total_likes FROM users WHERE users.id = :id", ['id' => $_SESSION['Id_Usuario']]);
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
    // Registro
    public function logout () {
        try {

        } catch (Exception $ex) {
            
        }
    }
    // Registro de nuevos usuarios
    public function signup (Request $request) {
        try {
            // Valida los datos recibidos
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:90',
                'lastname' => 'required',
                'email' => 'required|email|max:30|unique:users',
                'alias' => 'required|unique:users',
                'password' => [
                    'required',
                    'max:20',
                    'min:6',
                    'regex:/[A-Z].*[a-z].*\d|[A-Z].*\d.*[a-z]|[a-z].*[A-Z].*\d|[a-z].*\d.*[A-Z]|\d.*[A-Z].*[a-z]|\d.*[a-z].*[A-Z]/'
                ]
            ]);

            // Si hay errores...
            if ($validator->fails()) {
                $errors = $validator->errors();
                $msg = $errors->messages();
                if ($errors->has('email')) {
                   throw new \Exception($msg['email'][0]);
                } elseif ($errors->has('tokenID')) {
                    throw new \Exception($msg['tokenID'][0]);
                } elseif($errors->has('alias')) {
                    throw new \Exception($msg['alias'][0]);
                } elseif($errors->has('password')) {
                    throw new \Exception("La contraseña debe ser mayor a 6 caracteres, debe incluir una minuscula, una mayuscula y un numero");
                } else {
                    throw new \Exception($errors->first());
                }
            } else {
                
                // Si recibe como parametro el token de alguna red social
                if ($request->has('tokenID')) {
                    $tokenId = $request->input('tokenID');
                    $token_exists = User::where('tokenID', $tokenId)->first();
                    if ($token_exists) {
                        // Si el token ya esta registraod, devuelve error
                        throw new \Exception("Error, el usuario ya se encuentra registrado");
                    }
                }
                $username = '';
                if (strpos($request->input('alias'), '@') !== false) {
                    $username = $request->input('alias');
                } else {
                    $username = "@" . $request->input('alias');
                }

                $user = new User;
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->alias = $username;
                $user->password = hash('sha256', $request->input('password'));
                if ($request->has('tokenID')) {
                    $user->tokenID = $tokenId;
                }

                $user->save();
                return response()->json(['status' => 201, 'sucess' => true,  'message' => 'Usuario creado de manera exitosa', 'data' => $user], 201);
            }
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
