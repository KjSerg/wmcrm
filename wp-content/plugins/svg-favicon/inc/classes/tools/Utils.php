<?php

namespace FOF\SVGFAVICON\Tools;

class Utils
{
    public function arrayMapRecursive( $callback, $array )
    {
        if(empty($array)) return null;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->arrayMapRecursive($callback, $value);
            }
            else {
                $array[$key] = call_user_func($callback, $value);
            }
        }

        return $array;
    }

    public function extractKeys(&$arr, $keys): array
    {
        $extracted = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $arr)) {
                $extracted[$key] = $arr[$key];
                unset($arr[$key]);
            }
        }
        return $extracted;
    }

}