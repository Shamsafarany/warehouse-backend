<?php

namespace App\Domains\Identity\Presentation\Docs;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Identity - Authentication",
    description: "Endpoints for user registration, login, logout, token refresh, password reset, email verification, and profile management"
)]
class AuthOpenApi
{
    #[OA\Post(
        path: "/api/v1/auth/register",
        tags: ["Identity - Authentication"],
        summary: "Register a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["first_name", "last_name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "first_name", type: "string", example: "Sham"),
                    new OA\Property(property: "last_name", type: "string", example: "Al Safarany"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "sham@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "role", type: "string", enum: ["admin", "customer"], example: "customer")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "User registered successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 429, description: "Too many requests")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/login",
        tags: ["Identity - Authentication"],
        summary: "Authenticate user and get token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "sham@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Logged in successfully"),
            new OA\Response(response: 401, description: "Invalid credentials"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 429, description: "Too many requests")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/forgot-password",
        tags: ["Identity - Authentication"],
        summary: "Send password reset link to user email",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "sham@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Reset link sent successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 429, description: "Too many requests")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/reset-password",
        tags: ["Identity - Authentication"],
        summary: "Reset user password using token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "reset_token_string"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "sham@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "NewPassword123!"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "NewPassword123!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password changed successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 429, description: "Too many requests")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/logout",
        tags: ["Identity - Authentication"],
        summary: "Revoke current authentication token",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/refresh",
        tags: ["Identity - Authentication"],
        summary: "Refresh current authentication token",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Token refreshed successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Get(
        path: "/api/v1/auth/me",
        tags: ["Identity - Authentication"],
        summary: "Get authenticated user profile",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Profile retrieved successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/auth/profile",
        tags: ["Identity - Authentication"],
        summary: "Partially update user profile info",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "first_name", type: "string", example: "Sham Updated"),
                    new OA\Property(property: "last_name", type: "string", example: "Al Safarany"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "newemail@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Profile updated successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Patch(
        path: "/api/v1/auth/password",
        tags: ["Identity - Authentication"],
        summary: "Update user password",
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", format: "password", example: "oldpassword123"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "newpassword123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "newpassword123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password updated successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    #[OA\Get(
        path: "/api/v1/email/verify/{id}/{hash}",
        tags: ["Identity - Authentication"],
        summary: "Verify user email address via signed URL",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "User ID", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "hash", in: "path", required: true, description: "Email verification hash", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Email verified successfully"),
            new OA\Response(response: 400, description: "Invalid or expired signature")
        ]
    )]
    #[OA\Post(
        path: "/api/v1/auth/email/verification-notification",
        tags: ["Identity - Authentication"],
        summary: "Resend email verification notification",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Verification link sent successfully"),
            new OA\Response(response: 400, description: "Email already verified"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 429, description: "Too many requests")
        ]
    )]
    #[OA\Delete(
        path: "/api/v1/auth/profile",
        tags: ["Identity - Authentication"],
        summary: "Delete authenticated user account",
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Account deleted successfully"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function definitions() {}
}