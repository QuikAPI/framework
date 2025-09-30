# QuikAPI
  
  A lightweight, fast PHP microframework for building APIs quickly.
  
  [![Packagist Version](https://img.shields.io/packagist/v/quikapi/framework)](https://packagist.org/packages/quikapi/framework)
  ![PHP from Packagist](https://img.shields.io/packagist/php-v/quikapi/framework)
  [![Downloads](https://img.shields.io/packagist/dt/quikapi/framework)](https://packagist.org/packages/quikapi/framework)
  [![License](https://img.shields.io/github/license/QuikAPI/framework)](LICENSE)
  [![CI](https://github.com/QuikAPI/framework/actions/workflows/ci.yml/badge.svg?branch=master)](.github/workflows/ci.yml)
  
  - **Simple**: Minimal concepts. Define routes and controllers that return arrays.
  - **Fast**: Tiny core with zeroheavy dependencies at runtime.
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
# .env will be auto-copied by composer scripts; if not:
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
php quikapi make:controller User
php quikapi make:model User
php quikapi make:module Post
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

## Support & Policy

- **Branching**: Default branch is `master`. Dev stability via branch-alias `dev-master` → `0.1.x-dev`.
- **Versioning**: Semantic Versioning. Breaking changes only in major releases.
- **Backward compatibility**: No breaking changes in minor releases; deprecations announced one minor before removal.
- **Security**: See `SECURITY.md` and report privately before disclosure.

## License

MIT
