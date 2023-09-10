<?php

namespace ComfyPHP;

interface ToolsInterface
{
    public function useLog(mixed $data = null): mixed;
    public function useError(mixed $data = null): mixed;
    public function useFilter(mixed $data = null): mixed;
}

class Tools implements ToolsInterface
{
    protected \Closure $useLogF;
    protected \Closure $useErrorF;
    protected \Closure $useFilterF;

    public function __construct()
    {
        $this->initUseLog();
        $this->initUseError();
        $this->initUseFilter();
    }

    protected function initUseLog(): void
    {
        $this->useLogF = function (mixed $data): string{
            $jdata = json_encode($data);
            $content = "console.log($jdata);";
            $content = sprintf("<script>%s</script>", $content);
            return $content;
        };
    }

    // console log
    public function useLog(mixed $data = null): mixed
    {
        // declarations
        $useLog = $this->useLogF;

        // indirect function
        if (!$data) {
            return $useLog;
        }

        // direct function
        return $useLog($data);
    }

    protected function initUseError(): void
    {
        $this->useErrorF = function (mixed $data): string{
            $jdata = json_encode($data);
            $content = "console.error($jdata);";
            $content = sprintf("<script>%s</script>", $content);
            return $content;
        };
    }

    // console error
    public function useError(mixed $data = null): mixed
    {
        // declarations
        $useError = $this->useErrorF;

        // indirect function
        if (!$data) {
            return $useError;
        }

        // direct function
        return $useError($data);
    }

    protected function initUseFilterEncode(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, "initUseFilterEncode"], $data);
        }

        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, "UTF-8");
    }

    protected function initUseFilter(): void
    {
        $this->useFilterF = function (mixed $data): string {
            return $this->initUseFilterEncode($data);
        };
    }

    // html filter
    public function useFilter(mixed $data = null): mixed
    {
        // declarations
        $useFilter = $this->useFilterF;

        // indirect function
        if (!$data) {
            return $useFilter;
        }

        // direct function
        return $useFilter($data);
    }
}
