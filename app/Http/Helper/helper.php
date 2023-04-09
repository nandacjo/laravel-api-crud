<?php


if (!function_exists('p')) {
    function p($data)
    {
        echo "<prev>";
        print_r($data);
        echo "</prev>";
    }
}