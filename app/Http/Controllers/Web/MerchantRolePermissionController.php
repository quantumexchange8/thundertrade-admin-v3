<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerchantRolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($merchant, $role)
    {
        $permissions = PermissionGroup::with(['permissions'])->get();
        $rolePermissions = Permission::whereRelation('rolePermission', 'role_id', $role)
            ->whereRelation('rolePermission.role', 'merchant_id', $merchant)
            ->pluck('code');

        /*  $rolePermissions = collect($rolePermissions);
        $lists =   $permissions->mapWithKeys(function ($pr) use ($rolePermissions) {
            return [$pr['name'] => $rolePermissions->search($pr['name']) === false ? false : true];
        }); */

        //$lists = $lists->toArray();
        return Inertia::render('MerchantRolePermission', compact('permissions', 'rolePermissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($merchant, $role)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $merchant, $role)
    {
        $perm =  $request->permissions;
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
        return response()->json(['success' => true, 'message' => 'Update Success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($merchant, $role, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($merchant, $role, $id)
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
    public function update(Request $request, $merchant, $role, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($merchant, $role, $id)
    {
        //
    }
}
