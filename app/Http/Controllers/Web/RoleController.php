<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Role');
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
            'data' => ['merchants' => $merchants],
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
            'name' => ['required', 'string'],
            'merchant_id' => ['nullable', 'integer'],
        ]);

        Role::create([
            'name' => $request->name,
            'merchant_id' => $request->merchant_id,
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
        $merchants = Merchant::all();
        $role = Role::find($id);
        return response()->json([
            'success' => true,
            'data' => [
                'merchants' => $merchants,
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
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
