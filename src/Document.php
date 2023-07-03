<?php

namespace ComfyPHP;

interface DocumentInterface
{
    public function process(string $view): mixed;
    public function generate(string $head, string $body): string;
    public function notFound(): mixed;
}

class Document implements DocumentInterface
{
    public function __construct() {}

    protected function genDocument(): string
    {
        $root = $GLOBALS["ROOT"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];
        $docPath = "$root/$pagePath/_document.php";

        if (file_exists($docPath)) {
            $document = file_get_contents($docPath);
            return $document;
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
            return $document;
        }
    }

    // add params
    protected function addParams(string $input): string
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
    protected function minimize(string $code): string
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

    // return content: string (api) / array (head + body)
    public function process(string $view): mixed
    {
        $root = $GLOBALS["ROOT"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];

        // get processed view
        ob_start();
        include "$root/$pagePath/$view.php";
        $view_content = ob_get_clean();

        // head separation
        preg_match('/<head>(.*?)<\/head>/s', $view_content, $head_content);
        $head = isset($head_content[1]) ? $head_content[1] : "";

        // body separation
        preg_match('/<body>(.*?)<\/body>/s', $view_content, $body_content);
        $body = isset($body_content[1]) ? $body_content[1] : "";

        // count as API and return whole view
        if (!isset($head_content[1]) && !isset($body_content[1])) {
            return $view_content;
        }

        return array(
            "head" => $head,
            "body" => $body,
        );
    }

    // return document
    public function generate(string $head, string $body): string
    {
        // declarations
        $env = $_ENV["ENV"];
        $debug = $GLOBALS["SYSTEM_DEBUG"];
        $minimize = $GLOBALS["CONFIG_MINIMIZE"];

        // get document & add params
        $document = $this->addParams($this->genDocument());
        $head = $this->addParams($head);
        $body = $this->addParams($body);

        // regex
        $headRegex = "<!--%head%-->";
        $bodyRegex = "<!--%body%-->";

        // head regex replacement
        if (strpos($document, $headRegex)) {
            $document = str_replace($headRegex, $head, $document);
        } else {
            $document = ($debug ? trigger_error("$headRegex regex not found in document!") : "");
        }

        // body regex replacement
        if (strpos($document, $bodyRegex)) {
            $document = str_replace($bodyRegex, $body, $document);
        } else {
            $document = ($debug ? trigger_error("$bodyRegex regex not found in document!") : "");
        }

        // minimize
        if ($env === "development") {
            if (!$minimize) {
                return $document;
            }
        }

        return $this->minimize($document);
    }

    // return 404
    public function notFound(): mixed
    {
        $root = $GLOBALS["ROOT"];
        $pagePath = $GLOBALS["CONFIG_PAGE_PATH"];

        // custom 404 page
        if (file_exists("$root/$pagePath/_404.php")) {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            $contents = $this->process("_404");
            // count as API
            if (is_string($contents)) {
                return $contents;
            }
            // count as HTML
            return $this->generate($contents["head"], $contents["body"]);
        }
        // default 404
        else {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            return false;
        }
    }
}
