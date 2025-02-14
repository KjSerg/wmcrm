<?php

namespace FOF\SVGFAVICON\Tools;

class Ajax
{
    public function jsonHeader(array $obj){
        ob_start();
        echo json_encode($obj, JSON_NUMERIC_CHECK);
        header('Content-type: application/json');
        ob_end_flush();
    }
}