<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class MerchantUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($merchant)
    {
        return Inertia::render('MerchantUser');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($merchant)
    {

        $roles = Role::where('merchant_id', $merchant)->get();
        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $merchant)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'file_profile_picture' => ['nullable', 'file'],
            'role_id' => ['required', 'numeric'],
            'password' => ['required', Password::min(8)->mixedCase()]
        ]);

        $profile_picture = $request->file('file_profile_picture');
        if ($profile_picture) {
            $profile_picture = $profile_picture->store('uploads/profiles');
        }

        User::create([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'profile_picture' => $profile_picture,
            'role_id' => $request->role_id,
            'merchant_id' => $merchant,
            'password' => Hash::make($request->password)
        ]);


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
    public function show($merchant, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($merchant, $id)
    {
        $user = User::find($id);
        $roles = Role::where("merchant_id", $merchant)->get();
        return response()->json([
            'success' => true,
            'data' => [
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
    public function update(Request $request, $merchant, $id)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone,' . $id],
            'file_profile_picture' => ['nullable', 'file'],
            'role_id' => ['required', 'numeric'],
            'password' => ['nullable', Password::min(8)->mixedCase()]
        ]);

        $rec = User::find($id);

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
    public function destroy($merchant, $id)
    {
        $count = User::destroy($id);
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
