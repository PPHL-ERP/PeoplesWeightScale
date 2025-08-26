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
use Illuminate\Support\Facades\Hash;

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


    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email'    => ['required', 'string', 'max:100'], // can be email or username
        'password' => ['required', 'string', 'min:8', 'max:50'],
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $loginInput = $request->input('email');
    $loginField = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    $credentials = [$loginField => $loginInput, 'password' => $request->input('password')];

    try {
        if (! $token = JWTAuth::attempt($credentials)) {
            LogActivity::addToLog('Login failed: Wrong credentials.');
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }
    } catch (JWTException $e) {
        return response()->json(['success' => false, 'message' => 'Token creation failed.'], 500);
    }

    $user = auth()->user();

    if ($user->status != 1) {
        return response()->json(['success' => false, 'message' => 'Your account is not active.'], 403);
    }

    if ($user->isBanned == 1) {
        return response()->json(['success' => false, 'message' => 'Your account is banned.'], 403);
    }

    LogActivity::addToLog('Login successful for ' . $user->email);

    return response()->json([
        'success'    => true,
        'message'    => 'Login successful',
        'token'      => $token,
        'token_type' => 'bearer',
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
