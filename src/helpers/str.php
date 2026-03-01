<?php

if (!function_exists('str_snake')) {

    /**
     * @param string $string The input string.
     * @return string The snake_cased string.
     */
    function str_snake(string $string): string
    {
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        
        $string = preg_replace('/[\s-]+/', '_', $string);
        
        return strtolower($string);
    }
}