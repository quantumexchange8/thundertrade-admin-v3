<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerchantRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $merchant)
    {

        return Inertia::render('MerchantRole');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($merchant)
    {
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
            'name' => ['required', 'string'],
        ]);

        Role::create([
            'name' => $request->name,
            'merchant_id' => $merchant,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Create Role Success',
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
        $role = Role::find($id);
        return response()->json([
            'success' => true,
            'data' => [
                'details' => $role
            ],
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($merchant, $id)
    {
        $count = Role::destroy($id);
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete Role Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete Role Error",
            ]);
        }
    }
}
