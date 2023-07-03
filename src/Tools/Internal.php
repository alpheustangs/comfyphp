<?php

namespace ComfyPHP\Tools;

class Internal
{
    // comfy.config.php, $configs = [ ... ]
    public function checkConfigs(string $name, array $configs, array $extra = null): void
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
                $debug && trigger_error("$name: $configTitle must be a {$info[1]}!");
            }
        }
    }
}
