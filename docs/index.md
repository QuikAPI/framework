# QuikAPI

A lightweight, fast PHP microframework to build APIs quickly.

- Simple routing and controllers
- Eloquent ORM integration (optional)
- Request validation via Illuminate Validator
- Consistent JSON responses
- CLI scaffolding (models, controllers, requests, routes)

## Install

Option A — Create a fresh project (recommended, after package is on Packagist):

```bash
composer create-project quikapi/framework my-api
cd my-api
cp .env.example .env
```

Option B — Clone the repo and install deps:

```bash
composer install
cp .env.example .env
```

## Run (dev server)

```bash
php -S 127.0.0.1:8082 QuikAPI/server.php
```

Test:

```
GET http://127.0.0.1:8082/health
```

## CLI

```bash
php quikapi make:module Post \
  fillable="title,body,user_id" \
  relations="user:belongsTo:User,user_id,id;comments:hasMany:Comment,post_id,id"
```

This generates:
- Model `QuikAPI/Models/Post.php` (fillable, relations)
- Requests `QuikAPI/Requests/Post/{Index,Store,Update}Request.php`
- Controller `QuikAPI/Controllers/PostController.php`
- Routes appended to `QuikAPI/routes.php`


## Standard JSON Response

```
{
  "status": 200,
  "data": {},
  "errors": {},
  "hasError": false
}
```

On failure:
```
{
  "status": "fail",
  "data": {},
  "errors": ["..."],
  "hasError": true
}
```


