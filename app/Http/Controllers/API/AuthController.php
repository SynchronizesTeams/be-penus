<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // $input = $request->all();
        $input['password'] = bcrypt($request['password']);
        $user = User::create([
            'name' => $request->name,
            'password' => $request->password,
            'c_password' => $request->c_password,
            'user_id' => 'user'. '-' . uniqid(),
        ]);
        $success['token'] =  $user->createToken('penus-webapp')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'Berhasil daftar');
    }

    public function login(Request $request): JsonResponse
    {
        if(Auth::attempt(['name' => $request->name, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('penus-webapp')->plainTextToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'Berhasil Login');
        }
        else{
            return $this->sendError('Credential tidak valid', ['error'=>'Unauthorised']);
        }
    }
}
