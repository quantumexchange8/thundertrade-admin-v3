<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('User');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $merchants = Merchant::all();
        return response()->json([
            'success' => true,
            'data' => [
                'merchants' => $merchants
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'file_profile_picture' => ['nullable', 'file'],
            'role_id' => ['required', 'numeric'],
            'merchant_id' => ['required', 'numeric'],
            'password' => ['required', Password::min(8)->mixedCase()]
        ]);

        $profile_picture = $request->file('file_profile_picture');
        if ($profile_picture) {
            $profile_picture = $profile_picture->store('uploads/profiles');
        }

        LogBatch::startBatch();
        User::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'profile_picture' => $profile_picture,
            'role_id' => $request->role_id,
            'merchant_id' => $request->merchant_id,
            'password' => Hash::make($request->password)
        ]);

        activity('activity-log')->causedBy(Auth::user())->log('create-user');
        LogBatch::endBatch();
        return response()->json([
            'success' => true,
            'message' => 'Create User Success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $merchants = Merchant::where('id', $user->merchant_id)->get();
        $roles = Role::where("merchant_id", $user->merchant_id)->get();
        return response()->json([
            'success' => true,
            'data' => [
                'merchants' => $merchants,
                'details' => $user,
                'roles' => $roles
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone,' . $id],
            'file_profile_picture' => ['nullable', 'file'],
            'role_id' => ['required', 'numeric'],
            'merchant_id' => ['required', 'numeric'],
            'password' => ['nullable', Password::min(8)->mixedCase()]
        ]);

        $rec = User::find($id);
        LogBatch::startBatch();
        $rec->email = $request->email;
        $rec->name = $request->name;
        $rec->phone = $request->phone;
        $rec->role_id = $request->role_id;

        $profile_picture = $request->file('file_profile_picture');
        if ($profile_picture) {
            if ($rec->profile_picture) {
                File::delete($rec->profile_picture);
            }
            $profile_picture = $profile_picture->store('uploads/profiles');
            $rec->profile_picture = $profile_picture;
        }

        if ($request->password) {
            $rec->password =  Hash::make($request->password);
        }

        $rec->save();

        activity('activity-log')->causedBy(Auth::user())->log('edit-user');
        LogBatch::endBatch();
        return response()->json([
            'success' => true,
            'message' => 'Update User Success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        LogBatch::startBatch();
        $count = User::destroy($id);
        activity('activity-log')->causedBy(Auth::user())->log('');
        LogBatch::endBatch();
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete User Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete User Error",
            ]);
        }
    }
}
