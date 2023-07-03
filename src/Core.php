<?php

namespace ComfyPHP;

interface CoreInterface
{
    public function getRouter(): Router;
    public function getDocument(): Document;
    public function fileBasedRouter(): void;
    public function run(): void;
}

class Core implements CoreInterface
{
    protected Router $router;
    protected Document $document;
    private Tools\Internal $itools;

    public function __construct(array $params = [])
    {
        // fallback declarations
        $_ENV["ENV"] = "development";
        $GLOBALS["ROOT"] = $_SERVER["DOCUMENT_ROOT"] . "/..";
        $GLOBALS["CONFIG_VERSION"] = "1.0.0";
        $GLOBALS["CONFIG_MINIMIZE"] = false;
        $GLOBALS["CONFIG_PAGE_PATH"] = "src/pages";

        // set env
        $this->setEnv();

        // development
        if ($_ENV["ENV"] === "development") {
            $GLOBALS["SYSTEM_DEBUG"] = true;
        }
        // production
        else {
            $GLOBALS["SYSTEM_DEBUG"] = false;
        }

        $debug = $GLOBALS["SYSTEM_DEBUG"];

        // debug mode
        if ($debug) {
            error_reporting(E_ALL);
            ini_set("display_errors", "on");
        }

        // get dependencies
        $this->initDependencies($params);

        // set configs
        $this->setConfigs();
    }

    protected function initDependencies(array $params): void
    {
        $this->router = $params["router"] ?? new Router();
        $this->document = $params["document"] ?? new Document();
        $this->itools = new Tools\Internal();
    }

    protected function setEnv(): void
    {
        $root = $GLOBALS["ROOT"];
        $envHostPath = "$root/.comfyphp/.env";

        $envPath = "$root/.env";
        $envLocPath = "$root/.env.local";
        $envDevPath = "$root/.env.development";
        $envDevLocPath = "$root/.env.development.local";

        // .comfyphp/.env
        if (!file_exists($envHostPath)) {

            // priority from low to high
            $files = [
                $envDevPath,
                $envDevLocPath,
                $envPath,
                $envLocPath,
            ];
            $envContent = "";

            mkdir("$root/.comfyphp");

            $envContent .= 'ENV="development"' . "\n";

            foreach ($files as $file) {
                if (file_exists($file)) {
                    $fileContent = file_get_contents($file);
                    $envContent .= $fileContent . "\n";
                }
            }

            file_put_contents($envHostPath, $envContent);
        }

        // get contents of env file
        $ENVlines = file($envHostPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // insert env vars
        foreach ($ENVlines as $line) {
            // comment
            if (strpos($line, "#") === 0 || strpos($line, "//") === 0) {
                continue;
            }

            // convert
            if (strpos($line, "=") !== false) {
                list($key, $value) = explode("=", $line, 2);
                $key = trim($key);
                $value = trim($value, '"');
                $_ENV[$key] = $value;
            }
        }
    }

    protected function setConfigs(): void
    {
        $name = "comfy.config.php";
        $extra = [
            '// cors',
            'header("Access-Control-Allow-Origin: *");',
            'header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");',
            'header("Access-Control-Allow-Headers: Content-Type");',
            'header("Access-Control-Allow-Credentials: true");',
        ];
        $configs = [
            "CONFIG_VERSION" => ["version", "string", "1.0.0"],
            "CONFIG_MINIMIZE" => ["do document minimize in development mode", "boolean", false],
            "CONFIG_PAGE_PATH" => ["pages location", "string", "src/pages"],
        ];

        $this->itools->checkConfigs($name, $configs, $extra);
    }

    // get request method (get,post,put,delete)
    protected function getMethod(): string
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    // get pathname
    protected function getPath(): string
    {
        $path = $_SERVER["REQUEST_URI"] ?? "/";
        $position = strpos($path, "?");

        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    protected function resolve(): mixed
    {
        $method = $this->getMethod();
        $path = $this->getPath();
        $callback = $this->router->routes[$method][$path] ?? false;
        $doc = $this->document;

        // 404
        if ($callback === false) {
            return $doc->notFound();
        }

        // get view
        if (is_string($callback)) {
            // process
            $contents = $doc->process($callback);
            // count as API
            if (is_string($contents)) {
                return $contents;
            }
            // count as HTML
            return $doc->generate($contents["head"], $contents["body"]);
        }

        // call function
        return call_user_func($callback);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    // return router
    public function fileBasedRouter(): void
    {
        $this->router->fileBasedRouter();
    }

    // fire application
    public function run(): void
    {
        echo $this->resolve();
    }
}
