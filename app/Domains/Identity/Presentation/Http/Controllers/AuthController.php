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
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Domains\Identity\Presentation\Http\Requests\ForgotPasswordRequest;
use App\Domains\Identity\Presentation\Http\Requests\ResetPasswordRequest;

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
        try {
            $newToken = $action->execute($request->user());

            return $this->successResponse(
                [
                    'token' => $newToken,
                ],
                'تم تجديد الرمز بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تجديد الرمز: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return $this->successResponse(
                    null,
                    'البريد الإلكتروني مفعل مسبقاً'
                );
            }

            $request->fulfill();

            return $this->successResponse(
                null,
                'تم تفعيل البريد الإلكتروني بنجاح'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تفعيل البريد الإلكتروني: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return $this->errorResponse(
                    'البريد الإلكتروني مفعل مسبقاً',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $request->user()->sendEmailVerificationNotification();

            return $this->successResponse(
                null,
                'تم إرسال رابط التفعيل إلى بريدك الإلكتروني'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في إرسال رابط التفعيل: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // Send the password reset link using Laravel's password broker
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->successResponse(
                    null,
                    'تم إرسال رابط استعادة كلمة المرور إلى بريدك الإلكتروني.'
                );
            }

            return $this->errorResponse(
                'فشل في إرسال رابط استعادة كلمة المرور.',
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'حدث خطأ غير متوقع: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));
                    
                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->successResponse(
                    null,
                    'تم تغيير كلمة المرور بنجاح.'
                );
            }

            return $this->errorResponse(
                'رمز استعادة كلمة المرور غير صالح أو منتهي الصلاحية.',
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'فشل في تغيير كلمة المرور: ' . $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}