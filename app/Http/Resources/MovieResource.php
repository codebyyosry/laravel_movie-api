<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'title' => $this->resource['title'] ?? null,
            'overview' => $this->resource['overview'] ?? null,
            'release_date' => $this->resource['release_date'] ?? null,
            'rating' => $this->resource['vote_average'] ?? null,
            'vote_count' => $this->resource['vote_count'] ?? null,
            'poster_url' => isset($this->resource['poster_path'])
                ? 'https://image.tmdb.org/t/p/w500' . $this->resource['poster_path']
                : null,
            'backdrop_url' => isset($this->resource['backdrop_path'])
                ? 'https://image.tmdb.org/t/p/original' . $this->resource['backdrop_path']
                : null,
            'genre_ids' => $this->resource['genre_ids'] ?? [],
        ];
    }
}
