<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\LogBatch;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Permission');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $groups = PermissionGroup::all();
        return response()->json(['success' => true, 'data' => ['groups' => $groups]]);
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
            'group_id' => ['required', 'numeric'],
        ]);

        LogBatch::startBatch();
        Permission::create([
            'name' => $request->name,
            'group_id' => $request->group_id,
            'code' => Str::slug($request->name),
        ]);
        activity('activity-log')->causedBy(Auth::user())->log('create-permission');
        LogBatch::endBatch();
        return response()->json([
            'success' => true,
            'message' => 'Create Permission Success',
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
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $groups = PermissionGroup::all();
        $details = Permission::find($id);
        return response()->json([
            'success' => true,
            'data' => [
                'groups' => $groups,
                'details' => $details
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
            'name' => ['required', 'string'],
            'group_id' => ['required', 'numeric'],
        ]);

        LogBatch::startBatch();
        $rec = Permission::find($id);
        $rec->update([
            'name' => $request->name,
            'group_id' => $request->group_id,
            'code' => Str::slug($request->name),
        ]);
        activity('activity-log')->causedBy(Auth::user())->log('edit-permission');
        LogBatch::endBatch();
        return response()->json([
            'success' => true,
            'message' => 'Update Permission Success',
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
        $count = Permission::destroy($id);
        activity('activity-log')->causedBy(Auth::user())->log('delete-permission');
        LogBatch::endBatch();
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete Permission Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete Permission Error",
            ]);
        }
    }
}
