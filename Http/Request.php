<?php
namespace QuikAPI\Http;

class Request
{
    public string $method;
    public string $path;
    public array $headers = [];
    public array $query = [];
    public array $params = [];
    public array $body = [];
    public mixed $rawBody = null;
    // Placeholder for authenticated user object (set by auth middleware in future)
    public $authUser = null;

    public function __construct(
        ?string $method = null,
        ?string $path = null,
        ?array $headers = null,
        ?array $query = null,
        ?array $body = null,
        mixed $rawBody = null
    )
    {
        // Method & path
        $this->method = $method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = $path ?? (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

        // Headers & query
        $this->headers = $headers ?? $this->getAllHeaders();
        $this->query = $query ?? ($_GET ?? []);

        // Body
        $this->rawBody = $rawBody ?? file_get_contents('php://input');
        if ($body !== null) {
            $this->body = $body;
        } else {
            $contentType = $this->headers['Content-Type'] ?? $this->headers['content-type'] ?? '';
            if (stripos($contentType, 'application/json') !== false) {
                $decoded = json_decode($this->rawBody ?: '', true);
                if (is_array($decoded)) {
                    $this->body = $decoded;
                }
            } else {
                $this->body = $_POST ?? [];
            }
        }
    }

    public function input(string $key, $default = null)
    {
        if (array_key_exists($key, $this->body)) return $this->body[$key];
        if (array_key_exists($key, $this->query)) return $this->query[$key];
        return $default;
    }

    private function getAllHeaders(): array
    {
        if (function_exists('getallheaders')) {
            return getallheaders() ?: [];
        }
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }
        return $headers;
    }
}
