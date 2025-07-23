<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LogActivity;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use App\Http\Requests\user\UserRequest;
use App\Http\Controllers\Api\BaseController as BaseController;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class RegisterController extends BaseController
{
    use UploadAble;

    public function register(UserRequest $request)
    {

        try {
            $validatedData = $request->validated();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation Error.', $e->errors());
        }
        $valid = $validatedData;
        $valid['password'] = bcrypt($valid['password']);
        $user = new User();
        $user->fill($valid);
        if ($request->file('image')) {
            $filename = $this->uploadOne($request->image, 500, 500, config('imagepath.user'));
            $user->image = $filename;
        }
        $user->save();
        return $this->sendResponse($user, 'User registered successfully.');
    }


//    public function login(Request $request)
//    {
//        $request->validate([
//            'email' => 'required|string|email',
//            'password' => 'required|string',
//        ]);
//
//        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
//            $user = Auth::user();
//            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
//            $success['name'] =  $user->name;
//
//            return $this->sendResponse($success, 'User login successfully.');
//        } else {
//            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
//        }
//    }

//    public function login(Request $request)
//    {
//        $request->validate([
//            'email' => 'required|string|email',
//            'password' => 'required|string',
//        ]);
//        $credentials = $request->only('email', 'password');
//        $token = Auth::guard('api')->attempt($credentials);
//
//        if (!$token) {
//            return response()->json([
//                'message' => 'Unauthorized',
//            ], 401);
//        }
//
//        $user = Auth::user();
//        return response()->json([
//            'user' => $user,
//            'authorization' => [
//                'token' => $token,
//                'type' => 'bearer',
//            ]
//        ]);
//    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                LogActivity::addToLog('login failed!');
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'success' => false,
                'message' => 'Could not create token.',
            ], 500);
        }
        LogActivity::addToLog('login Successful!');
        //Token created, return with success response and jwt token
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }


    public function update(UserRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation Error.', $e->errors());
        }
        $user = User::find($id);



        if (!$user) {
            return $this->sendError('User not found.');
        }
        $user->update($validatedData);
        if ($request->hasFile('image')) {
            $filename = $this->uploadOne($request->image, 500, 500, config('imagepath.user'));
            $this->deleteOne(config('imagepath.user'), $user->image);
            $user->update(['image' => $filename]);
        }
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
            $user->save();
        }
        return $this->sendResponse($user, 'User updated successfully.');
    }


    public function logoutOLD(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);
            LogActivity::addToLog('Logout Successful!');
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout
        try {
            JWTAuth::invalidate($request->token);
            LogActivity::addToLog('Logout Successful!');
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




//    public function logout()
//    {
//        auth('api')->logout();
//        return response()->json([
//            'message' => 'Successfully logged out'
//        ]);
//    }
//
//    public function refresh()
//    {
//        return $this->respondWithToken(auth('api')->refresh());
//    }
//
//    protected function respondWithToken($token)
//    {
//        return response()->json([
//            'access_token' => $token,
//            'token_type' => 'bearer',
//            'expires_in' => config('jwt.ttl') * 60
//        ]);
//    }


}
