<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            $users = User::all();
            if ($users->isEmpty()) {
                return $this->errorResponse('No users found', [], 404);
            }
            return $this->successResponse('Users retrieved successfully', $users, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Something went wrong while retrieving users', $th->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|min:3|max:25',
                'email' => 'required|email|min:3|max:25|unique:users,email',
                'password' => 'required|string|min:6|max:25'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            if (!$user) {
                return $this->errorResponse('User registration failed', [], 500);
            }
            return $this->successResponse('User registered successfully', $user, 201);
        } catch (\Throwable $th) {
            return $this->errorResponse('Something went wrong during user registration', $th->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found', [], 404);
            }
            return $this->successResponse('User retrieved successfully', $user, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Something went wrong while retrieving the user', $th->getMessage(), 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $rules = [
                'name' => 'required|string|min:3|max:25',
                'email' => 'required|email|min:3|max:25|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6|max:25'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found', [], 404);
            }
            $updated = $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password
            ]);
            if (!$updated || !$user->wasChanged()) {
                return $this->errorResponse('No changes detected, user not updated', [], 200);
            }
            return $this->successResponse('User updated successfully', $user, 202);
        } catch (\Throwable $th) {
            return $this->errorResponse('Something went wrong during user update', $th->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->delete()) {
                return $this->successResponse('User deleted successfully', [], 200);
            }
            return $this->errorResponse('Failed to delete the user', [], 500);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('User not found', [], 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('Something went wrong while deleting the user', $th->getMessage(), 500);
        }
    }
}
