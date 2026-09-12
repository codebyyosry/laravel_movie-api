<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Movie REST API",
    description: "A Laravel backend-only REST API wrapping TMDB, with JWT authentication, caching, and a personal favorites system.\n\n"
        . "**Links:** [GitHub](https://github.com/codebyyosry) • [Medium](https://medium.com/@yosry.jobs)",
    contact: new OA\Contact(
        name: "Yosry Badr",
        email: "yosry.jobs@gmail.com",
        url: "https://codebyyosry.github.io/#contact"
    )
)]
#[OA\Server(
    url: "http://localhost:8001",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\PathItem(
    path: "/api/v1"
)]
#[OA\Tag(
    name: "Movies",
    description: "Browse and search movies (public)"
)]
#[OA\Tag(
    name: "Genres",
    description: "List movie genres (public)"
)]
#[OA\Tag(
    name: "Auth",
    description: "Register, login, and manage authentication"
)]
#[OA\Tag(
    name: "Favorites",
    description: "Authenticated users manage their favorite movies"
)]

class OpenApi
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
