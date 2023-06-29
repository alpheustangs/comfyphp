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
    public function all($path, $callback)
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
    public function post($path, $callback)
    {
        $this->routes["post"][$path] = $callback;
    }

    // read
    public function get($path, $callback)
    {
        $this->routes["get"][$path] = $callback;
    }

    // update
    public function put($path, $callback)
    {
        $this->routes["put"][$path] = $callback;
    }

    // update
    public function patch($path, $callback)
    {
        $this->routes["patch"][$path] = $callback;
    }

    // delete
    public function delete($path, $callback)
    {
        $this->routes["delete"][$path] = $callback;
    }

    // head
    public function head($path, $callback)
    {
        $this->routes["head"][$path] = $callback;
    }

    // options
    public function options($path, $callback)
    {
        $this->routes["options"][$path] = $callback;
    }

    // trace
    public function trace($path, $callback)
    {
        $this->routes["trace"][$path] = $callback;
    }

    // trace
    public function connect($path, $callback)
    {
        $this->routes["connect"][$path] = $callback;
    }
}
