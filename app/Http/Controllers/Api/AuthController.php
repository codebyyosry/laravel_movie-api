<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/auth/register",
        summary: "Register a new user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Ahmed"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "ahmed@test.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "123456"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "123456")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "User registered, returns JWT"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = Auth::guard('api')->login($user);

        return $this->respondWithToken($token, $user);
    }

    #[OA\Post(
        path: "/api/auth/login",
        summary: "Login and receive a JWT",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "ahmed@test.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "123456")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login successful, returns JWT"),
            new OA\Response(response: 401, description: "Invalid credentials")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return $this->respondWithToken($token, Auth::guard('api')->user());
    }

    #[OA\Get(
        path: "/api/auth/me",
        summary: "Get the currently authenticated user",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Current user data"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Auth::guard('api')->user(),
        ]);
    }

    #[OA\Post(
        path: "/api/auth/logout",
        summary: "Logout the current user (invalidate token)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Successfully logged out")
        ]
    )]
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    #[OA\Post(
        path: "/api/auth/refresh",
        summary: "Refresh the JWT token",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "New JWT issued")
        ]
    )]
    public function refresh(): JsonResponse
    {
        $token = Auth::guard('api')->refresh();

        return $this->respondWithToken($token, Auth::guard('api')->user());
    }

    protected function respondWithToken(string $token, User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            ],
        ]);
    }
}
