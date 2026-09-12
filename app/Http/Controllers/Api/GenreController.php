<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TMDBService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class GenreController extends Controller
{
    protected TMDBService $tmdb;

    public function __construct(TMDBService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    #[OA\Get(
        path: "/api/genres",
        summary: "Get the list of movie genres",
        tags: ["Genres"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of genres",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 28),
                                    new OA\Property(property: "name", type: "string", example: "Action")
                                ]
                            )
                        )
                    ]
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $data = $this->tmdb->getGenres();

        return response()->json([
            'success' => true,
            'data' => $data['genres'],
        ]);
    }
}
