<?php

namespace ComfyPHP;

interface RouterInterface
{
    public function fileBasedRouter(): void;
    public function all(string $path, string | callable $callback): void;
    public function post(string $path, string | callable $callback): void;
    public function get(string $path, string | callable $callback): void;
    public function put(string $path, string | callable $callback): void;
    public function patch(string $path, string | callable $callback): void;
    public function delete(string $path, string | callable $callback): void;
    public function head(string $path, string | callable $callback): void;
    public function options(string $path, string | callable $callback): void;
    public function trace(string $path, string | callable $callback): void;
    public function connect(string $path, string | callable $callback): void;
}

class Router implements RouterInterface
{
    public array $routes = [];

    public function __construct() {}

    // get pathname
    protected function getPathName(): string
    {
        $url = $_SERVER["REQUEST_URI"];
        $path = parse_url($url, PHP_URL_PATH);
        $haveParams = strpos($path, "?");

        $pathName = "";

        // check if params
        if ($haveParams) {
            $pathName = substr($path, 0, $haveParams);
        } else {
            $pathName = $path;
        }

        return $pathName;
    }

    // file-based routing
    public function fileBasedRouter(): void
    {
        $root = $GLOBALS["ROOT"];
        $pathName = $this->getPathName();
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];
        $filePath = "";

        // index
        if ($pathName === "/") {
            $this->all($pathName, "index");
            return;
        }

        /*
        example:
        /_document
        /_404
        /_init
         */
        // denied access of page start with underscore
        if (strpos($pathName, "_") === 1) {
            return;
        }

        // page exist
        if (file_exists("$root/$pagePath/$pathName.php")) {
            $filePath = $pathName;
        }
        // page not exist
        else {
            // page/index exist
            if (file_exists("$root/$pagePath/$pathName/index.php")) {
                $filePath = "$pathName/index";
            }
            // page/index not exist
            else {
                return;
            }
        }

        $this->all($pathName, $filePath);
        return;
    }

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
