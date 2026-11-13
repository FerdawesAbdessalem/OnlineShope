<?php

declare(strict_types=1);

namespace App;

/** Minimal router: "GET /product/{id}" → callable(array $params). */
final class Router
{
    /** @var array<int, array{string, string, callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    /** @return array{0: ?callable, 1: array<string,string>, 2: bool} handler, params, methodNotAllowed */
    public function match(string $method, string $uri): array
    {
        $path = '/' . trim((string) parse_url($uri, PHP_URL_PATH), '/');
        $allowedElsewhere = false;
        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($routePath, '/') ?: '/') . '$#';
            if (!preg_match($pattern, $path, $m)) {
                continue;
            }
            if ($routeMethod !== $method) {
                $allowedElsewhere = true;
                continue;
            }
            return [$handler, array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY), false];
        }
        return [null, [], $allowedElsewhere];
    }
}
