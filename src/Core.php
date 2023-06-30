<?php

namespace ComfyPHP;

class Core
{
    public string $document;
    public Http $http;
    public Tool $tool;

    public function __construct()
    {
        // declarations - class
        $this->http = new Http();
        $this->tool = new Tool();

        // declarations
        $_ENV["ENV"] = "development";
        $GLOBALS["ROOT"] = $_SERVER["DOCUMENT_ROOT"] . "/..";
        $GLOBALS["CONFIG_VERSION"] = "1.0.0";
        $GLOBALS["CONFIG_MINIMIZE"] = false;
        $GLOBALS["CONFIG_PAGE_PATH"] = "src/pages";

        // get env
        $this->getEnvs();

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

        // get configuration
        $this->getConfigs();

        // get _document.php
        $this->getDocument();
    }

    private function getEnvs(): void
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

        $ENVlines = file($envHostPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

    private function getConfigs(): void
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

        $this->setConfigs($name, $configs, $extra);
    }

    // comfy.config.php, $configs = [ ... ]
    public function setConfigs(string $name, array $configs, array $extra = null): void
    {
        $root = $GLOBALS["ROOT"];
        $path = "$root/$name";

        // config not exist
        if (!file_exists($path)) {
            $content = "<?php\n\n";

            if ($extra) {
                $lastExtra = end($extra);
                reset($extra);
                foreach ($extra as $line) {
                    if ($line !== $lastExtra) {
                        $content .= $line . "\n";
                    }
                    // last line
                    else {
                        $content .= $line . "\n\n";
                    }
                }
            }

            foreach ($configs as $title => $info) {
                // declarations
                $comment = $info[0];
                $type = $info[1];
                $value = $info[2];

                $value = ($type === "dynamic") ? $value : var_export($value, true);
                $value = str_replace("'", '"', $value);
                $content .=
                    "// $comment\n" .
                    '$GLOBALS["' . $title . '"] = ' . $value . ";\n\n";
            }

            file_put_contents($path, $content);
        }

        require_once $path;

        // config fallback
        foreach ($configs as $configTitle => $info) {
            if ($info[1] === "dynamic") {
                continue;
            } elseif (gettype($GLOBALS[$configTitle]) !== $info[1]) {
                $GLOBALS[$configTitle] = $info[2];
                $debug && $this->tool->error("comfy.lang.config.php: $configTitle must be a {$info[1]}!");
            }
        }
    }

    public function getDocument(): void
    {
        $root = $GLOBALS["ROOT"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];
        $docPath = "$root/$pagePath/_document.php";

        if (file_exists($docPath)) {
            $document = file_get_contents($docPath);
            $this->document = $document;
        } else {
            $document =
                "<!DOCTYPE html>" . "\n" . "\n" .
                "<html>" . "\n" . "\n" .
                "<head>" . "\n" .
                '   <meta charset="UTF-8">' . "\n" .
                '   <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />' . "\n" .
                "   <!--%head%-->" . "\n" .
                "</head>" . "\n" . "\n" .
                "<body>" . "\n" .
                "   <!--%body%-->" . "\n" .
                "</body>" . "\n" . "\n" .
                "</html>" . "\n";
            $this->document = $document;
        }
    }

    // get request method (get,post,put,delete)
    public function getMethod(): string
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    // get pathname
    public function getPath(): string
    {
        $path = $_SERVER["REQUEST_URI"] ?? "/";
        $position = strpos($path, "?");

        if ($position === false) {
            return $path;
        }

        return substr($path, 0, $position);
    }

    // add params
    public function addParams(string $input): string
    {
        $env = $_ENV["ENV"];
        $ver = $GLOBALS["CONFIG_VERSION"];
        $script_pattern = '/<script\s+src="([^"]+)"><\/script>/i';
        $style_pattern = '/<link\b(?:\s+[^>]*)?\srel="stylesheet"(?:\s+[^>]*)?\shref="([^"]+)"\s*\/?>/i';
        $param = $env === "development" ? "?ts=" . time() : "?ver=" . $ver;

        $output1 = preg_replace_callback($script_pattern, function ($matches) use ($param) {
            return sprintf('<script src="%s%s"></script>', $matches[1], $param);
        }, $input);

        $output2 = preg_replace_callback($style_pattern, function ($matches) use ($param) {
            return sprintf('<link rel="stylesheet" href="%s%s" />', $matches[1], $param);
        }, $output1);

        return $output2;
    }

    // minimize
    public function minimize(string $code): string
    {
        // match
        $patterns = array(
            // space at the beginning of lines
            '/(\n|^)(\x20+|\t)/',
            // single-line comments
            '/(\/\/|<!--)(.*?)(\n|$|-->)/',
            // multiple line comments
            '/\/\*(.*?)\*\//us',
            // remove new lines
            '/\n/',
            // spaces (Without \n)
            '/(\x20+|\t)/',
            // spaces between tags
            '/\>\s+\</',
            // spaces between quotation ("') and end tags
            '/(\"|\')\s+\>/',
            // spaces between = "'
            '/=\s+(\"|\')/',
        );

        // replacements
        $replace = array(
            "\n",
            "\n",
            "",
            " ",
            " ",
            "><",
            "$1>",
            "=$1",
        );

        $code = preg_replace($patterns, $replace, $code);

        return $code;
    }

    // generate document
    public function gen(string $head, string $body): string
    {
        // declarations
        $env = $_ENV["ENV"];
        $tool = $this->tool;
        $debug = $GLOBALS["SYSTEM_DEBUG"];
        $minimize = $GLOBALS["CONFIG_MINIMIZE"];
        $document = $this->addParams($this->document);
        $head = $this->addParams($head);
        $body = $this->addParams($body);

        $headRegex = "<!--%head%-->";
        $bodyRegex = "<!--%body%-->";

        // head regex replacement
        if (strpos($document, $headRegex)) {
            $document = str_replace($headRegex, $head, $document);
        } else {
            $document = ($debug ? $tool->error("$headRegex regex not found in document!") : "");
        }

        // body regex replacement
        if (strpos($document, $bodyRegex)) {
            $document = str_replace($bodyRegex, $body, $document);
        } else {
            $document = ($debug ? $tool->error("$bodyRegex regex not found in document!") : "");
        }

        // minimize
        if ($env === "development") {
            if (!$minimize) {
                return $document;
            }
        }

        return $this->minimize($document);
    }

    // page rendering
    public function renderPage(string $page): string
    {
        $root = $GLOBALS["ROOT"];
        $env = $_ENV["ENV"];
        $debug = $GLOBALS["SYSTEM_DEBUG"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];

        ob_start();
        include "$root/$pagePath/$page.php";
        $file_contents = ob_get_clean();

        preg_match('/<head>(.*?)<\/head>/s', $file_contents, $head_matches);
        $head = isset($head_matches[1]) ? $head_matches[1] : "";

        preg_match('/<body>(.*?)<\/body>/s', $file_contents, $body_matches);
        $body = isset($body_matches[1]) ? $body_matches[1] : "";

        if (!isset($head_matches[1]) && !isset($body_matches[1])) {
            return $file_contents;
        }

        return $this->gen($head, $body);
    }

    // return 404
    public function notFound(): string
    {
        $root = $GLOBALS["ROOT"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];

        if (file_exists("$root/$pagePath/_404.php")) {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            return $this->renderPage("_404");
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            return "";
        }
    }

    // file-based routing
    public function fileBasedRouter(): ?string
    {
        $http = $this->http;
        $root = $GLOBALS["ROOT"];
        $url = $_SERVER["REQUEST_URI"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];

        $pathName = "";
        $filePath = "";

        $path = parse_url($url, PHP_URL_PATH);
        $haveParams = strpos($path, "?");

        // check if params
        if ($haveParams) {
            $pathName = substr($path, 0, $haveParams);
        } else {
            $pathName = $path;
        }

        // index
        if ($pathName === "/") {
            return $http->all($pathName, "index");
        }

        /*
        example:
        /_document
        /_404
        /_init
         */
        // denied access of page start with underscore
        if (strpos($pathName, "_") === 1) {
            return $this->notFound();
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
                return $this->notFound();
            }
        }

        return $http->all($pathName, $filePath);
    }

    // path resolve
    public function resolve(): string
    {
        $http = $this->http;
        $method = $this->getMethod();
        $path = $this->getPath();
        $callback = $http->routes[$method][$path] ?? false;

        // 404
        if ($callback === false) {
            return $this->notFound();
        }

        // get view
        if (is_string($callback)) {
            return $this->renderPage($callback);
        }

        // call function
        return call_user_func($callback);
    }

    // fire application
    public function run(): void
    {
        echo $this->resolve();
    }

}
