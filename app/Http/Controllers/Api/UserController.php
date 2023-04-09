<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($flag)
    {
        // flag => 1 (Active)
        // flag => 0 (All)
        // All users (Acitve and Inactive)
        // Active
        $query = User::select('email', 'name');
        if ($flag == 1) {
            $query->where('status', 1);
        } elseif ($flag == 0) {
            $query->where('status', 0);
        } else {
            return response()->json([
                'message' => 'Invalid parameter passed, it can be either 1 or 0',
                'status' => 0
            ], 400);
        }
        $users = $query->get();
        if (count($users) > 0) {
            $response = [
                'message' => count($users) . ' Users found',
                'status' => 1,
                'data' => $users
            ];
        } else {
            $response = [
                'message' => count($users) . ' users found',
                'status' => 0
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                // 'password' => Hash::make($request->password)
                'password' => $request->password
            ];
            DB::beginTransaction();
            try {
                $user = User::create($data);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $user = null;
            }

            if ($user != null) {
                // okay
                return response()->json([
                    'message' => 'User registred successfully'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Internal server error'
                ], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'user note found',
                'status' => 0
            ];
        } else {
            $response = [
                'message' => 'user found',
                'staus' => 1,
                'data' => $user
            ];
        }
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            //user does not exists
            return response()->json([
                'status' => 0,
                'message' => 'User does not exists'
            ], 400);
        } else {
            DB::beginTransaction();
            try {
                $user->name = $request['name'];
                $user->email = $request['email'];
                $user->contact = $request['contact'];
                $user->pincode = $request['pincode'];
                $user->address = $request['address'];
                $user->save();
                DB::commit();
            } catch (\Exception $err) {
                DB::rollBack();
                $user = null;
            }

            if (is_null($user)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Internal server error',
                    'error_msg' => $err->getMessage(),
                ], 500);
            } else {
                return response()->json([
                    'status' => 1,
                    'message' => 'Data updated successfully'
                ], 200);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => "user doesn't exist",
                'status' => 0
            ];

            $resCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => 'User deleted succesfully',
                    'status' => 1
                ];
                $resCode = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => "Internal server error",
                    'status' => 0
                ];
                $resCode = 500;
            }
        }
        return response()->json($response, $resCode);
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                'status' => 0,
                'message' => 'Password does not exists'
            ], 500);
        } else {
            // main -> change password code
            if ($user->password == $request['old_password']) {

                if ($request['new_password'] == $request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $user->password = $request['confirm_password'];
                        $user->save();
                        DB::commit();
                    } catch (\Exception $err) {
                        $user = null;
                        DB::rollBack();
                    }

                    if (is_null($user)) {
                        return response()->json([
                            'status' => 0,
                            'message' => 'Internal server error',
                            'error_msg' => $err->getMessage(),
                        ], 500);
                    } else {
                        return response()->json([
                            'status' => 1,
                            'message' => 'Password updated successfully'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => 0,
                        'message' => 'New password and confirm password does not match',
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Old password does not match'

                ], 400);
            }
        }
    }
}