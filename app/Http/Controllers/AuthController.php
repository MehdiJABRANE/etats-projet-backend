<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; 
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'Nom' => 'required|string',
            //'Mot_Passe' => 'required|string',
        ]);
    
        $user = User::where('Nom', $request->input('Nom'))->first();
    
        if ($user) {
            //if ($request->input('Mot_Passe') === $user->Mot_Passe) {
            log::info($user);
                try {
                    $token = JWTAuth::fromUser($user);
                    log::info($token);
    
                    $cookieUser = cookie('userID', json_encode($user->Cle), 60); 
                    $cookieToken = cookie('token', $token, 60);
                    
    
                    return response()->json([
                        'success' => true,
                        'userName'   => $user->Nom,
                        'userId'   => $user->Cle,
                        "Description" => $user->Description
                    ])->withCookie(cookie('token', $token, 60, '/', null, true, true, false, 'None'))
                    ->withCookie(cookie('userID', json_encode($user->Cle), 60, '/', null, true, true, false, 'None'));
    
                } catch (JWTException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not create token',
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
        //} 
        // else {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid credentials',
        //     ], 401);
        // }
    }
    
    public function logout(Request $request)
    {
        try {
            // Invalidate the token (if it's a stateless token, this step can be optional)
            JWTAuth::invalidate(JWTAuth::getToken());
    
            // Clear the cookies by setting them to null
            $cookieUser = cookie('userID', null, -1, '/', null, false, true, false, 'None'); 
            $cookieToken = cookie('token', null, -1, '/', null, false, true, false, 'None');
    
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ])->withCookie($cookieToken,$cookieUser);
            
        } catch (JWTException $e) {
            Log::error('Failed to logout: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again',
            ], 500);
        }
    }
}
