<?php

namespace App\Http\Controllers\Api\Extension;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ExtensionAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'الحساب غير نشط.'], 403);
        }

        if (! $user->can('question-import')) {
            return response()->json(['message' => 'ليس لديك صلاحية استيراد الأسئلة.'], 403);
        }

        $allowedRoles = config('extension.allowed_roles', []);
        if ($allowedRoles !== [] && ! $user->hasAnyRole($allowedRoles)) {
            return response()->json(['message' => 'هذه الإضافة مخصصة لفريق الإدارة والتدريس.'], 403);
        }

        $user->tokens()->where('name', config('extension.token_name'))->delete();

        $token = $user->createToken(
            config('extension.token_name'),
            ['extension:import']
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'تم تسجيل الخروج.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    protected function userPayload(\App\Models\User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ];
    }
}
