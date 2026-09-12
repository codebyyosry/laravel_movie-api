<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'title' => $this->resource['title'] ?? null,
            'tagline' => $this->resource['tagline'] ?? null,
            'overview' => $this->resource['overview'] ?? null,
            'release_date' => $this->resource['release_date'] ?? null,
            'runtime' => $this->resource['runtime'] ?? null,
            'rating' => $this->resource['vote_average'] ?? null,
            'vote_count' => $this->resource['vote_count'] ?? null,
            'budget' => $this->resource['budget'] ?? null,
            'revenue' => $this->resource['revenue'] ?? null,
            'status' => $this->resource['status'] ?? null,
            'poster_url' => isset($this->resource['poster_path'])
                ? 'https://image.tmdb.org/t/p/w500' . $this->resource['poster_path']
                : null,
            'backdrop_url' => isset($this->resource['backdrop_path'])
                ? 'https://image.tmdb.org/t/p/original' . $this->resource['backdrop_path']
                : null,
            'genres' => collect($this->resource['genres'] ?? [])->map(fn($g) => [
                'id' => $g['id'],
                'name' => $g['name'],
            ]),
            'production_companies' => collect($this->resource['production_companies'] ?? [])->map(fn($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
            ]),
        ];
    }
}
