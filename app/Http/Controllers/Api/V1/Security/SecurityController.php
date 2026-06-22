<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FailedLoginAttempt;

class SecurityController extends Controller
{
    public function loginHistory(Request $request)
    {
        return response()->json(
            $request->user()
                ->loginHistories()
                ->latest()
                ->paginate()
        );
    }

    public function failedLogins()
    {
        return response()->json(
            FailedLoginAttempt::latest()
                ->paginate()
        );
    }
}
