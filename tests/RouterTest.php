<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use QuikAPI\Http\Router;
use QuikAPI\Http\Request;

class RouterTest extends TestCase
{
    public function testNotFoundReturns404Structure(): void
    {
        $router = new Router();
        $req = new Request('GET', '/unknown');
        $res = $router->dispatch($req);
        $this->assertSame(['error' => 'Not Found', 'path' => '/unknown'], $res);
    }

    public function testMethodNotAllowedListsAllowed(): void
    {
        $router = new Router();
        $router->post('/items', fn($r) => ['ok' => true]);
        $res = $router->dispatch(new Request('GET', '/items'));
        $this->assertSame('Method Not Allowed', $res['error'] ?? null);
        $this->assertContains('POST', $res['allow'] ?? []);
    }

    public function testRouteParamsAreExtracted(): void
    {
        $router = new Router();
        $router->get('/users/{id}', fn(Request $r) => ['id' => $r->params['id'] ?? null]);
        $res = $router->dispatch(new Request('GET', '/users/42'));
        $this->assertSame(['id' => '42'], $res);
    }

    public function testMiddlewareWrapsHandler(): void
    {
        $router = new Router();
        $router->use(function (Request $req, $next) {
            $req->params['mw'] = 'global';
            return $next($req);
        });
        $router->get('/ping', function (Request $req) {
            return ['pong' => true, 'mw' => $req->params['mw'] ?? null];
        });
        $res = $router->dispatch(new Request('GET', '/ping'));
        $this->assertSame(['pong' => true, 'mw' => 'global'], $res);
    }
}
