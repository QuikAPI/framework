# QuikAPI

A lightweight, fast PHP microframework for building APIs quickly.

- **Simple**: Minimal concepts. Define routes and controllers that return arrays.
- **Fast**: Tiny core with zero heavy dependencies at runtime.
- **Modern**: PSR-4, middleware, JSON-first, PDO-ready, `.env` support.
- **Productive**: Built-in CLI to scaffold controllers/models/modules.

## Requirements

- PHP >= 8.1
- ext-PDO (e.g., pdo_mysql)

## Installation

Option A — Create a fresh project (recommended):

```bash
composer create-project quikapi/framework my-api
cd my-api
cp .env.example .env
```

Option B — Use in an existing repo:

```bash
composer install
cp .env.example .env
```

Then edit `.env` for your DB and CORS settings.

## Quickstart (Dev Server)

Run QuikAPI on PHP's built-in server:

```bash
php -S 127.0.0.1:8082 QuikAPI/server.php
```

Visit `http://127.0.0.1:8082/health` →

```json
{"status":"ok","time":"..."}
```

## Routing

Add routes in `QuikAPI/routes.php`:

```php
use QuikAPI\Controllers\HealthController;

$router->get('/health', [HealthController::class, 'index']);
```

Supported verbs: `get`, `post`, `put`, `patch`, `delete`.
Path params: `/users/{id}` → available in `$req->params['id']`.

## Controllers

Controllers are simple classes returning arrays. Example:

```php
namespace QuikAPI\Controllers;

use QuikAPI\Http\Request;

class UserController {
    public function index(Request $req): array { return ['items' => []]; }
    public function show(Request $req): array { return ['id' => $req->params['id'] ?? null]; }
}
```

## Middleware

Global middleware is registered in `QuikAPI/index.php`:

- `QuikAPI\Middleware\ErrorHandler` → JSON error responses
- `QuikAPI\Middleware\Cors` → CORS headers / OPTIONS

You can also pass route-specific middleware in `Router::add()`.

## Database (PDO)

Configure in `.env`:

```
DB_DSN=mysql:host=127.0.0.1;dbname=app;charset=utf8mb4
DB_USER=root
DB_PASS=
```

Use `QuikAPI\Database\Connection::get()` to obtain a shared PDO instance.

## Security

Use `QuikAPI\Security\Password` for password hashing and verification:

```php
$hash = Password::hash($plain);
$ok = Password::verify($plain, $hash);
```

## CLI

Built-in simple CLI to scaffold modules:

```bash
php QuikAPI/cli.php make:controller User
php QuikAPI/cli.php make:model User
php QuikAPI/cli.php make:module Post
```

Or after `composer install`, you can use the bin:

```bash
php bin/quikapi make:module Post
```

This will append REST routes to `QuikAPI/routes.php` and create the controller/model.

## Project Structure

```
QuikAPI/
  Controllers/
  Database/
  Http/
  Middleware/
  Security/
  routes.php
  index.php
  server.php
  cli.php
```

## Roadmap

- Symfony Console based CLI (route:list, serve, etc.)
- Request/Response interfaces and typed responses
- Validation utilities
- Auth middleware (JWT/session)

## License

MIT
