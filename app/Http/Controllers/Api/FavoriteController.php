<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Services\TMDBService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class FavoriteController extends Controller
{
    protected TMDBService $tmdb;

    public function __construct(TMDBService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    #[OA\Get(
        path: "/api/favorites",
        summary: "List the authenticated user's favorite movies",
        tags: ["Favorites"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "List of favorites"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index(): JsonResponse
    {
        $favorites = Auth::guard('api')->user()
            ->favorites()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    #[OA\Post(
        path: "/api/favorites",
        summary: "Add a movie to favorites",
        tags: ["Favorites"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["tmdb_movie_id"],
                properties: [
                    new OA\Property(property: "tmdb_movie_id", type: "integer", example: 155)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Added to favorites"),
            new OA\Response(response: 409, description: "Movie already in favorites"),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tmdb_movie_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::guard('api')->user();
        $movieId = $request->tmdb_movie_id;

        $existing = Favorite::where('user_id', $user->id)
            ->where('tmdb_movie_id', $movieId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Movie already in favorites',
            ], 409);
        }

        $movie = $this->tmdb->getMovieDetails($movieId);

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'tmdb_movie_id' => $movieId,
            'title' => $movie['title'] ?? null,
            'poster_path' => $movie['poster_path'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Added to favorites',
            'data' => $favorite,
        ], 201);
    }

    #[OA\Delete(
        path: "/api/favorites/{tmdbMovieId}",
        summary: "Remove a movie from favorites",
        tags: ["Favorites"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "tmdbMovieId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 155)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Removed from favorites"),
            new OA\Response(response: 404, description: "Favorite not found")
        ]
    )]
    public function destroy(int $tmdbMovieId): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('tmdb_movie_id', $tmdbMovieId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites',
        ]);
    }
}
