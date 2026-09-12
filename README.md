![Tests](https://github.com/codebyyosry/laravel_movie-api/actions/workflows/tests.yml/badge.svg)

# 🎬 Movie REST API

A Laravel-based backend-only REST API that wraps [The Movie Database (TMDB)](https://www.themoviedb.org/)
with authentication, caching, and a personal favorites system.

## Features

- 🔍 Browse popular movies, search, and view details (proxied from TMDB)
- 🎭 Genre listing
- 🔐 JWT authentication (register, login, logout, token refresh)
- ⭐ Authenticated users can manage a personal favorites list
- ⚡ Response caching to reduce TMDB API calls and speed up responses
- 🚦 Rate limiting on public and auth endpoints
- 🧪 Automated tests (SQLite in-memory + mocked TMDB responses)
- 📘 Interactive API documentation (Swagger / OpenAPI)
- 🐳 Fully containerized with Docker (app + Nginx + MySQL)
- 🔁 Continuous Integration via GitHub Actions (tests run automatically on every push)

## Tech Stack

- Laravel 11 (API-only, no Blade/frontend)
- MySQL (development database)
- SQLite (testing database)
- JWT (`tymon/jwt-auth`) for authentication
- TMDB API for movie data
- Docker & Docker Compose (containerized app, web server, and database)
- Swagger / OpenAPI (`darkaonline/l5-swagger`) for interactive API docs
- GitHub Actions for CI (automated test runs on every push)

## Setup

### Option A — Run locally (PHP + MySQL on your machine)

1. Clone the repo and install dependencies:
   \`\`\`bash
   composer install
   \`\`\`
2. Copy `.env.example` to `.env` and fill in:
    - Database credentials (MySQL)
    - `TMDB_TOKEN` (get one free at https://www.themoviedb.org/settings/api)
3. Generate keys:
   \`\`\`bash
   php artisan key:generate
   php artisan jwt:secret
   \`\`\`
4. Run migrations:
   \`\`\`bash
   php artisan migrate
   \`\`\`
5. Serve the app:
   \`\`\`bash
   php artisan serve
   \`\`\`
6. Run tests:
   \`\`\`bash
   php artisan test
   \`\`\`

### Option B — Run with Docker (recommended, no local PHP/MySQL needed)

1. Copy the Docker env file and fill in `TMDB_TOKEN`:
   \`\`\`bash
   cp .env.docker.example .env.docker
   \`\`\`
2. Build and start the containers (Laravel app, Nginx, MySQL):
   \`\`\`bash
   docker compose --env-file .env.docker up -d --build
   \`\`\`
3. Generate keys inside the container:
   \`\`\`bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan jwt:secret
   \`\`\`
4. Run migrations:
   \`\`\`bash
   docker compose exec app php artisan migrate
   \`\`\`
5. Visit the API at `http://localhost:8001/api/movies/popular`
6. Run tests inside the container:
   \`\`\`bash
   docker compose exec app php artisan test
   \`\`\`

## API Endpoints

| Method | Endpoint                         | Auth Required | Description                  |
| ------ | --------------------------------- | -------------- | ---------------------------- |
| GET    | `/api/movies/popular`            | No             | List popular movies          |
| GET    | `/api/movies/search?query=`      | No             | Search movies                |
| GET    | `/api/movies/{id}`               | No             | Movie details                |
| GET    | `/api/genres`                    | No             | List genres                  |
| POST   | `/api/auth/register`             | No             | Register a new user          |
| POST   | `/api/auth/login`                | No             | Login and receive JWT        |
| GET    | `/api/auth/me`                   | Yes            | Get current user             |
| POST   | `/api/auth/logout`               | Yes            | Logout                       |
| POST   | `/api/auth/refresh`              | Yes            | Refresh JWT token             |
| GET    | `/api/favorites`                 | Yes            | List user's favorite movies  |
| POST   | `/api/favorites`                 | Yes            | Add a movie to favorites     |
| DELETE | `/api/favorites/{tmdb_movie_id}` | Yes            | Remove from favorites        |

**Rate limits:**

- `movies` & `genres` endpoints: 60 requests/minute per IP
- `auth` endpoints (register/login): 5 requests/minute per IP

## Postman Collection

A ready-to-use Postman collection is included at [`postman_collection.json`](./postman_collection.json), covering every endpoint above.

**How to use:**

1. Open Postman → **Import** → select `postman_collection.json`
2. Check the collection's **Variables** tab and confirm `base_url` matches your local server (default: `http://127.0.0.1:8001/api`)
3. Run the **Auth → Register** or **Auth → Login** request first — it automatically saves the JWT into the `access_token` collection variable
4. All other protected requests (Favorites, Me, Logout) are then authenticated automatically — no manual token copy-pasting needed

## API Documentation (Swagger / OpenAPI)

Interactive, browsable API docs are auto-generated from annotations in the controllers using `darkaonline/l5-swagger`.

- **View the docs:** `http://localhost:8001/api/documentation`
- **Regenerate after changing annotations:**
  \`\`\`bash
  php artisan l5-swagger:generate
  # or, if running via Docker:
  docker compose exec app php artisan l5-swagger:generate
  \`\`\`
- Click **Authorize** in the Swagger UI and paste a JWT to test protected endpoints (Favorites, Me, Logout, Refresh) directly from the browser.

## Continuous Integration (CI)

Every push and pull request automatically triggers a GitHub Actions workflow (`.github/workflows/tests.yml`) that:

1. Checks out the code and sets up PHP 8.3
2. Installs Composer dependencies
3. Generates the application key and JWT secret
4. Runs the full test suite (`php artisan test`) against an in-memory SQLite database, with TMDB calls mocked — no real network calls, no secrets required in CI

Test status is visible at the top of this README, and in the repo's **Actions** tab.

## Development Journey

This project was built incrementally, step by step, following a deliberate architecture-first approach rather than writing code ad-hoc. Below is the order in which it was developed:

1. **Project setup** — Installed Laravel configured as API-only (no Blade views, no frontend build tooling), with `routes/api.php` as the single entry point for all endpoints.

2. **Database strategy** — Configured two separate database connections: MySQL for real local development, and SQLite in-memory specifically for the automated test suite, so tests run fast and never touch real data.

3. **Local schema design** — Designed and migrated the `favorites` table (linking users to TMDB movie IDs), while intentionally keeping actual movie data un-persisted, since TMDB remains the source of truth.

4. **TMDB integration (Service Layer)** — Built a dedicated `TMDBService` class to isolate all external API communication. This kept controllers thin and made TMDB calls easy to mock during testing.

5. **Caching layer** — Wrapped every TMDB call in `Cache::remember()` with TTLs tuned to data volatility (popular movies: 1 hour, movie details: 6 hours, genres: 24 hours), reducing external calls and speeding up repeated requests.

6. **REST endpoints & API Resources** — Built controllers for movies and genres, then introduced `MovieResource`/`MovieDetailResource` to shape clean, consistent JSON output instead of exposing TMDB's raw response structure.

7. **JWT Authentication** — Integrated `tymon/jwt-auth`, configured the `api` guard, and built register/login/logout/refresh/me endpoints, with middleware protecting authenticated routes.

8. **Favorites feature** — Combined JWT auth + TMDB data + local persistence: authenticated users can add, list, and remove favorite movies, with movie titles/posters cached locally to avoid redundant TMDB calls.

9. **Automated testing** — Wrote Feature tests covering the full auth flow, favorites CRUD, and movie browsing endpoints, using SQLite in-memory and `Http::fake()` to mock TMDB responses — ensuring tests are fast, deterministic, and network-independent.

10. **Centralized exception handling** — Configured a single exception handler (in `bootstrap/app.php`) so all API routes consistently return clean JSON error responses (`401`, `422`, `404`, `500`, etc.) instead of Laravel's default HTML error pages — even when hit directly from a browser.

11. **Production-readiness polish** — Added rate limiting (stricter on auth endpoints to prevent brute-force attempts, more lenient on public browsing endpoints), a Postman collection for easy testing, and documentation.

12. **Containerization (Docker)** — Wrote a `Dockerfile` (PHP-FPM), an Nginx config, and a `docker-compose.yml` wiring together the app, web server, and a MySQL container, so the entire stack can be spun up with a single command regardless of the host machine's local environment.

13. **API Documentation (Swagger/OpenAPI)** — Integrated `darkaonline/l5-swagger` and annotated every controller method, producing an interactive, always-up-to-date API reference at `/api/documentation`.

14. **Continuous Integration (GitHub Actions)** — Added a workflow that installs dependencies and runs the full automated test suite on every push/PR, using the same SQLite + mocked-TMDB testing strategy from local development — giving immediate feedback if a change breaks something.

**Key decisions along the way:**

- Chose JWT over Sanctum for a stateless, token-based auth flow suited to a pure API (no Sanctum config/dependencies were kept in the final project).
- Kept the project strictly backend-only — no `resources/views`, no `routes/web.php`, no frontend build tooling (Vite/npm) — to stay focused on API design.
- Used a Service Layer pattern for TMDB rather than calling the HTTP client directly in controllers, which paid off directly during testing (clean mocking via `Http::fake()`).
- Containerized the app early with Docker so the exact same environment runs locally, in CI, and could later be deployed to any host without configuration drift.

## Architecture Notes

- **Service layer pattern**: All TMDB communication is isolated in `App\Services\TMDBService`,
  keeping controllers thin and making TMDB calls easy to mock in tests.
- **API Resources**: Responses are shaped via `MovieResource`/`MovieDetailResource` rather than
  exposing TMDB's raw structure.
- **Caching**: TMDB responses are cached with different TTLs based on data volatility
  (popular movies: 1hr, details: 6hr, genres: 24hr).
