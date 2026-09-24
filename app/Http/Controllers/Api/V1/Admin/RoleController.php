<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        // Get both global roles (hotel_id = null) and hotel-specific roles
        $teamId = getPermissionsTeamId();
        
        $roles = Role::with('permissions')
            ->where(function ($query) use ($teamId) {
                $query->whereNull('hotel_id')
                      ->orWhere('hotel_id', $teamId);
            })
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        if ($request->boolean('is_global') && $request->user()->hasRole('super_admin')) {
            setPermissionsTeamId(null);
        }

        // team_id (hotel_id) is automatically appended by Spatie based on getPermissionsTeamId()
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
        if ($request->boolean('is_global') && $request->user()->hasRole('super_admin')) {
            $role->hotel_id = null;
        }

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
