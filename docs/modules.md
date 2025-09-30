# Modules and Auth (User example)

This guide shows how to build a module and secure it with JWT auth. A ready-made User module is included.

## Prerequisites

- Configure DB in `.env` (Eloquent): `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- Set JWT settings in `.env`:
  ```
  JWT_SECRET=please-change-me
  JWT_ALG=HS256
  JWT_ISS=quikapi
  ```
- Ensure the `users` table exists (example minimal schema):
  ```sql
  CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
  );
  ```

## Auth middleware

`QuikAPI/Middleware/Auth.php` reads Bearer JWT from `Authorization` header and sets `Request->authUser`.

- Issue tokens with `QuikAPI/Security/JWT::encode(['sub' => $userId], '+2 hours')`
- Validate tokens with `JWT::decode($token)`

Auth middleware is registered in `QuikAPI/index.php` globally.

## User module structure

- Model: `QuikAPI/Models/User.php` (Eloquent, `fillable`, `hidden`)
- Requests: `QuikAPI/Requests/User/{Index,Store,Update}Request.php`
- Controller: `QuikAPI/Controllers/UserController.php`
- Routes: defined in `QuikAPI/routes.php`

## Endpoints

- `POST /auth/login` → Login with `{ email, password }`. Returns `{ token, token_type, expires_in }`.
- `GET /users` → Paginated list. Supports query options: `per_page`, `operations`, `select`, `order_by`, `order_type`, `group_by`, `return_type`, `with`.
- `GET /users/{id}` → Show one user.
- `POST /users` → Create user. Body: `{ name, email, password }`.
- `PUT /users/{id}` → Update user. Body supports partial fields; `password` will be hashed if present.
- `DELETE /users/{id}` → Delete user.

All responses follow the standard schema from `QuikAPI/Support/Responses.php`.

## Query operations helper

Use `operations` array to filter queries via `QuikAPI/Support/QueryOps::addOperationsInQuery()`.
Example:
```json
{
  "per_page": 10,
  "operations": [
    { "code": "where", "parameters": { "column": "email", "operator": "like", "value": "%@example.com" } },
    { "code": "where_null", "parameters": { "column": "deleted_at" } }
  ],
  "order_by": "id",
  "order_type": "desc"
}
```

## Protecting routes

The Auth middleware runs globally. In controllers, you can check `if (!$request->authUser) { /* return 401 */ }` for endpoints that require auth.

## Generate your own module

Use the CLI to scaffold:
```bash
php quikapi make:module Post \
  fillable="title,body,user_id" \
  relations="user:belongsTo:User,user_id,id;comments:hasMany:Comment,post_id,id"
```
This creates model, requests, controller and routes with standard behavior.
