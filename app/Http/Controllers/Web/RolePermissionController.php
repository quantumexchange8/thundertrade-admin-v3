<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\LogBatch;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($role)
    {
        $permissions = PermissionGroup::with(['permissions'])->get();
        $rolePermissions = Permission::whereRelation('rolePermission', 'role_id', $role)->pluck('code');
        return Inertia::render('RolePermission', compact('permissions', 'rolePermissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($role)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $role)
    {
        $perm =  $request->permissions;
        LogBatch::startBatch();
        $permissions = Permission::whereIn('code', $perm)->pluck('id');
        foreach ($permissions as $row) {
            RolePermission::firstOrCreate([
                'role_id' => $role,
                'permission_id' => $row
            ]);
        }
        RolePermission::where('role_id', $role)->whereRelation('permission', function ($q) use ($permissions) {
            $q->whereNotIn('id', $permissions);
        })->delete();
        activity('activity-log')->causedBy(Auth::user())->log('edit-role-permission');
        LogBatch::endBatch();
        return response()->json(['success' => true, 'message' => 'Update Success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($role, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($role, $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $role, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($role, $id)
    {
        //
    }
}
