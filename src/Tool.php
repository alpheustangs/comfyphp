<?php

namespace ComfyPHP;

class Tool
{
    // console log
    public function log($data)
    {
        $jdata = json_encode($data);
        $content = "console.log($jdata);";
        $content = sprintf("<script>%s</script>", $content);
        echo $content;
        return;
    }

    // console error
    public function error($data)
    {
        $jdata = json_encode($data);
        $content = "console.error($jdata);";
        $content = sprintf("<script>%s</script>", $content);
        echo $content;
        return;
    }
}
