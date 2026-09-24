<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        // Spatie automatically scopes this to the active team/hotel based on getPermissionsTeamId()
        $roles = Role::with('permissions')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        // team_id (hotel_id) is automatically appended by Spatie if teams is true
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data' => $role->load('permissions'),
        ], 201);
    }

    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => $role->load('permissions'),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data' => $role->load('permissions'),
        ]);
    }

    public function destroy(Role $role)
    {
        // Protect system roles from deletion (if needed, but usually scoped to hotel anyway)
        // If they are hotel specific, they are just custom roles.
        
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
