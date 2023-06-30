<?php

namespace ComfyPHP;

class Http
{

    public array $routes = [];

    public function __construct()
    {
        $this->routes;
    }

    // all
    public function all(string $path, string | callable $callback): void
    {
        $this->routes["post"][$path] = $callback;
        $this->routes["get"][$path] = $callback;
        $this->routes["put"][$path] = $callback;
        $this->routes["patch"][$path] = $callback;
        $this->routes["delete"][$path] = $callback;
        $this->routes["head"][$path] = $callback;
        $this->routes["options"][$path] = $callback;
        $this->routes["trace"][$path] = $callback;
        $this->routes["connect"][$path] = $callback;
    }

    // create
    public function post(string $path, string | callable $callback): void
    {
        $this->routes["post"][$path] = $callback;
    }

    // read
    public function get(string $path, string | callable $callback): void
    {
        $this->routes["get"][$path] = $callback;
    }

    // update
    public function put(string $path, string | callable $callback): void
    {
        $this->routes["put"][$path] = $callback;
    }

    // update
    public function patch(string $path, string | callable $callback): void
    {
        $this->routes["patch"][$path] = $callback;
    }

    // delete
    public function delete(string $path, string | callable $callback): void
    {
        $this->routes["delete"][$path] = $callback;
    }

    // head
    public function head(string $path, string | callable $callback): void
    {
        $this->routes["head"][$path] = $callback;
    }

    // options
    public function options(string $path, string | callable $callback): void
    {
        $this->routes["options"][$path] = $callback;
    }

    // trace
    public function trace(string $path, string | callable $callback): void
    {
        $this->routes["trace"][$path] = $callback;
    }

    // trace
    public function connect(string $path, string | callable $callback): void
    {
        $this->routes["connect"][$path] = $callback;
    }
}
