<?php

namespace App\Domains\Identity\Presentation\Http\Controllers;

use App\Domains\Identity\Application\Actions\DeleteUserAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Domains\Identity\Presentation\Http\Requests\RegisterRequest;
use App\Domains\Identity\Presentation\Http\Requests\LoginRequest;
use App\Domains\Identity\Presentation\Http\Resources\UserResource;
use App\Domains\Identity\Application\Actions\RegisterUserAction;
use App\Domains\Identity\Application\Actions\LoginUserAction;
use App\Domains\Identity\Application\Actions\LogoutUserAction;
use App\Domains\Identity\Application\Actions\RefreshTokenAction;
use App\Domains\Identity\Application\Actions\UpdateUserPasswordAction;
use App\Domains\Identity\Application\Actions\UpdateUserProfileAction;
use App\Domains\Identity\Presentation\Http\Requests\UpdatePasswordRequest;
use App\Domains\Identity\Presentation\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->validated());

            return $this->successResponse(
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                'تم إنشاء الحساب بنجاح',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في إنشاء الحساب: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        try {
            $result = $action->execute($request->validated());

            return $this->successResponse(
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                ],
                'تم تسجيل الدخول بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage() ?: 'فشل في تسجيل الدخول.',
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        try {
            $action->execute($request);

            return $this->successResponse(
                null,
                'تم تسجيل الخروج بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تسجيل الخروج: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new UserResource($request->user()),
                'تم استرجاع بيانات الحساب بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في استرجاع بيانات الحساب: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function updateProfile(UpdateProfileRequest $request, UpdateUserProfileAction $action): JsonResponse
    {
        try {
            $user = $action->execute($request->user(), $request->validated());

            return $this->successResponse(
                new UserResource($user),
                'تم تحديث البيانات الشخصية بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('فشل في تحديث البيانات: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdateUserPasswordAction $action): JsonResponse
    {
        try {
            $action->execute($request->user(), $request->password);

            return $this->successResponse(
                null,
                'تم تغيير كلمة المرور بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('فشل في تغيير كلمة المرور: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy(Request $request, DeleteUserAccountAction $action): JsonResponse
    {
        try {
            $action->execute($request->user());

            return $this->successResponse(
                null,
                'تم حذف الحساب بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('فشل في حذف الحساب: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function refresh(Request $request, RefreshTokenAction $action): JsonResponse
    {
        $newToken = $action->execute($request->user());

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'تم تجديد الرمز بنجاح',
            'data' => [
                'token' => $newToken,
            ],
        ], Response::HTTP_OK);
    }
}