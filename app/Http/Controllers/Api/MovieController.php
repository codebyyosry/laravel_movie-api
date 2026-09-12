<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieDetailResource;
use App\Http\Resources\MovieResource;
use App\Services\TMDBService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MovieController extends Controller
{
    protected TMDBService $tmdb;

    public function __construct(TMDBService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    #[OA\Get(
        path: "/api/movies/popular",
        summary: "Get a list of popular movies",
        tags: ["Movies"],
        parameters: [
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                description: "Page number for pagination",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "A paginated list of popular movies",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 155),
                                    new OA\Property(property: "title", type: "string", example: "The Dark Knight"),
                                    new OA\Property(property: "overview", type: "string", example: "Batman raises the stakes..."),
                                    new OA\Property(property: "release_date", type: "string", example: "2008-07-16"),
                                    new OA\Property(property: "rating", type: "number", format: "float", example: 8.5),
                                    new OA\Property(property: "poster_url", type: "string", example: "https://image.tmdb.org/t/p/w500/abc.jpg")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            type: "object",
                            properties: [
                                new OA\Property(property: "page", type: "integer", example: 1),
                                new OA\Property(property: "total_pages", type: "integer", example: 500),
                                new OA\Property(property: "total_results", type: "integer", example: 10000)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function popular(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);

        $data = $this->tmdb->getPopularMovies($page);

        return response()->json([
            'success' => true,
            'data' => MovieResource::collection(collect($data['results'])),
            'meta' => [
                'page' => $data['page'],
                'total_pages' => $data['total_pages'],
                'total_results' => $data['total_results'],
            ],
        ]);
    }

    #[OA\Get(
        path: "/api/movies/search",
        summary: "Search for movies by title",
        tags: ["Movies"],
        parameters: [
            new OA\Parameter(
                name: "query",
                in: "query",
                required: true,
                description: "Search text",
                schema: new OA\Schema(type: "string", example: "batman")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Matching movies"),
            new OA\Response(response: 422, description: "Missing or invalid query parameter")
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $page = (int) $request->query('page', 1);
        $query = $request->query('query');

        $data = $this->tmdb->searchMovies($query, $page);

        return response()->json([
            'success' => true,
            'data' => MovieResource::collection(collect($data['results'])),
            'meta' => [
                'page' => $data['page'],
                'total_pages' => $data['total_pages'],
                'total_results' => $data['total_results'],
            ],
        ]);
    }

    #[OA\Get(
        path: "/api/movies/{id}",
        summary: "Get details for a single movie",
        tags: ["Movies"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "TMDB movie ID",
                schema: new OA\Schema(type: "integer", example: 155)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Movie details"),
            new OA\Response(response: 404, description: "Movie not found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $data = $this->tmdb->getMovieDetails($id);

        return response()->json([
            'success' => true,
            'data' => new MovieDetailResource($data),
        ]);
    }
}
