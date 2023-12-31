<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "username" => 'required|string|max:255',
            "email" => 'required|email|max:255',
            "password" => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 422); //validation not correct
        }
        $password = bcrypt($request->password);
     
        User::create([
            "username" => $request->username,
            "email" => $request->email,
            "password" => $password,
           
        ]);

        return response()->json([
            "success" => "You Registered successfully",
            
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => 'required|email|max:255',
            "password" => 'required|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 422);
        }

        $user = User::where("email", $request->email)->first();
        if ($user !== null) {
            $oldpasword = $user->password;

            $access_token =JWT::encode([
                'username' => $request->username,
                'email' => $request->email,
            ], 'your_secret_key', 'HS256');

            $isVerfied = Hash::check($request->password, $oldpasword);
            if ($isVerfied) {
                $user->update([
                    "access_token" => $access_token
                ]);

                return response()->json([
                    "success" => 'welcome User Logged in successfully',
                    "access_token" => $access_token    /////////////
                ], 200);

            } else {
                return response()->json([
                    "msg" => "crediniatials not correct"
                ], 404);
            }


        } else {
            return response()->json([
                "error" => "This account does not exist"
            ], 404);
        }
    }

    public function logout(Request $request)
    {
        $access_token=$request->header("access_token");
        if ($access_token !==null) {
            $user=User::where("access_token",$access_token)->first();
            if ($user !==null) {
                $user->update([
                    "access_token"=>null
                ]);
                return response()->json([
                    'msg' => 'Logged Out successfully'
                ]);
            }else {
                return response()->json([
                    "msg"=>"access token not correct"
                ]);
            }
        }else {
            return response()->json([
                "msg"=>"access token not found"
            ]);
        }
    }
}
