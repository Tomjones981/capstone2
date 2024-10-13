<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Faculty;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function user(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        if ($user->user_type == "admin" || $user->user_type == "payroll") {
            $result = User::where('id', $userId)->first();
        } else {
            $result = null;
        }

        return response()->json($result);
    }




    public function index()
    {
        return UserResource::collection(
            User::query()->orderBy('id', 'desc')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response(new UserResource($user), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response('', 204);
    }
    public function getUser($userId)
    {
        $user = User::findOrFail($userId);
        return response()->json(['user' => $user]);
    }

    public function getUserProfile()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $faculty = Faculty::where('user_id', $user->id)->first();

        if ($faculty) {
            $user->image = $faculty->image;
            $user->first_name = $faculty->first_name;
        } else {
            $user->image = null;
            $user->first_name = null;
        }

        return response()->json($user);
    }

}
