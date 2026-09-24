<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        // Permissions are global, so we just list them all
        $permissions = Permission::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
