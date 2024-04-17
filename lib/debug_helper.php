<?php

/**
 * Does a var_dump with pre
 * @param array $arr is the array that will be var_dump'd
 * @param boolean $dump is the boolean that will be var_dump, false will error log
 * @return mixed The last insert ID for single insert or number of rows affected for bulk insert.
 * 
 * @author Ethan
 * @version 0.1 04/17/2024
 */

function dump($arr, $dump = true)
{
    if ($dump) {
        echo "<pre>";
        echo var_dump($arr);
        echo "</pre>";
    } else {
        error_log($arr);
    }
}
