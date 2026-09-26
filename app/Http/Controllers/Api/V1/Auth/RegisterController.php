<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class RegisterController extends Controller
{
    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: 'Register a new Company/Tenant and admin user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registration successful'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // 1. Create the Tenant (Company)
            $tenant = Tenant::create([
                'name' => $request->company_name,
                'email' => $request->email,
                'phone' => $request->phone, // stored in JSON data col automatically by stancl
                'is_active' => true,
            ]);

            // 2. Create the Domain for the Tenant
            $tenant->domains()->create([
                'domain' => $request->domain,
            ]);

            // 3. Initialize Tenancy for the current scope so User creation works correctly
            tenancy()->initialize($tenant);

            // 4. Create the initial Admin User
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]);

            // Assign the super admin role
            // The role might need to be created if it doesn't exist for this tenant,
            // or if roles are global, we just assign it.
            // Assuming Spatie permissions handles this.
            // $user->assignRole('Super Admin'); // Uncomment or handle roles based on your setup

            $token = $user->createToken('hotelia-pms')->plainTextToken;

            return response()->json([
                'message' => 'Company registered successfully',
                'tenant_id' => $tenant->id,
                'token' => $token,
                'user' => $user,
            ], 201);
        });
    }
}
