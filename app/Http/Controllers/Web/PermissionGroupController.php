<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render("PermissionGroup");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'name' => ['required', 'string']
        ]);

        PermissionGroup::create([
            'name' => $request->name,
            'code' => Str::slug($request->name),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Create Permission Group Success',
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
        $permissionGroup = PermissionGroup::find($id);
        return response()->json(['success' => true, 'data' => $permissionGroup]);
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
            'name' => ['required', 'string']
        ]);

        $rec = PermissionGroup::find($id);
        $rec->update([
            'name' => $request->name,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Update Permission Group Success',
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
        $count = PermissionGroup::destroy($id);
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete Permission Group Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete Permission Group Error",
            ]);
        }
    }
}
