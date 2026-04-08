<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function __construct(
        private AuditLogService $audit,
    ) {}

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->audit->log(
            userId: $user->id,
            action: 'password_updated',
            tableName: 'users',
            recordId: $user->id,
            oldValues: [
                'password' => '[REDACTED_OLD_PASSWORD]',
            ],
            newValues: [
                'password' => '[REDACTED_NEW_PASSWORD]',
            ],
        );

        return back()->with('status', 'password-updated');
    }
}
