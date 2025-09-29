<?php
namespace QuikAPI\Http;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function add(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
            'regex' => $this->compilePath($path),
            'params' => $this->extractParamNames($path),
        ];
    }

    public function get(string $path, callable|array $handler, array $middleware = []): void { $this->add('GET', $path, $handler, $middleware); }
    public function post(string $path, callable|array $handler, array $middleware = []): void { $this->add('POST', $path, $handler, $middleware); }
    public function put(string $path, callable|array $handler, array $middleware = []): void { $this->add('PUT', $path, $handler, $middleware); }
    public function patch(string $path, callable|array $handler, array $middleware = []): void { $this->add('PATCH', $path, $handler, $middleware); }
    public function delete(string $path, callable|array $handler, array $middleware = []): void { $this->add('DELETE', $path, $handler, $middleware); }

    public function use(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(Request $request)
    {
        $method = strtoupper($request->method);
        $path = rtrim($request->path, '/') ?: '/';
        $allowed = [];
        foreach ($this->routes as $m => $routes) {
            foreach ($routes as $route) {
                if (preg_match($route['regex'], $path)) {
                    $allowed[] = $m;
                    if ($m !== $method) {
                        continue;
                    }
                    // match with correct method
                    preg_match($route['regex'], $path, $matches);
                    $params = [];
                    foreach ($route['params'] as $i => $name) {
                        $params[$name] = $matches[$i + 1] ?? null;
                    }
                    $request->params = $params;
                    $handler = $this->wrapMiddleware($route['handler'], array_merge($this->middleware, $route['middleware']));
                    return $handler($request);
                }
            }
        }
        if (!empty($allowed)) {
            http_response_code(405);
            return ['error' => 'Method Not Allowed', 'allow' => array_values(array_unique($allowed))];
        }
        http_response_code(404);
        return ['error' => 'Not Found', 'path' => $path];
    }

    private function compilePath(string $path): string
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '([\\w-]+)', rtrim($path, '/') ?: '/');
        return '#^' . $regex . '$#';
    }

    private function extractParamNames(string $path): array
    {
        preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $path, $m);
        return $m[1] ?? [];
    }

    private function wrapMiddleware(callable|array $handler, array $middleware): callable
    {
        $core = function (Request $req) use ($handler) {
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $instance = is_string($class) ? new $class() : $class;
                return $instance->$method($req);
            }
            return $handler($req);
        };

        return array_reduce(
            array_reverse($middleware),
            function ($next, $mw) {
                return function (Request $req) use ($mw, $next) {
                    return $mw($req, $next);
                };
            },
            $core
        );
    }
}
