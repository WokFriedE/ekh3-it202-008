<?php

/** Safe Echo Function
 * Takes in a value and passes it through htmlspecialchars()
 * or
 * Takes an array, a key, and default value and will return the value from the array if the key exists or the default value.
 * Can pass a flag to determine if the value will immediately echo or just return so it can be set to a variable
 */

 // pass array or object, the key (if exist), default value, and boolean to actually echo --> either return or echo
function se($v, $k = null, $default = "", $isEcho = true)
{
    // return value from key
    if (is_array($v) && isset($k) && isset($v[$k])) {
        $returnValue = $v[$k];
    // return value from object 
    } else if (is_object($v) && isset($k) && isset($v->$k)) {
        $returnValue = $v->$k;
    } else {
        $returnValue = $v;
        //added 07-05-2021 to fix case where $k of $v isn't set
        //this is to kep htmlspecialchars happy

        // if array or obj
        if (is_array($returnValue) || is_object($returnValue)) {
            $returnValue = $default;
        }
    }
    // use default value if not set yet
    if (!isset($returnValue)) {
        $returnValue = $default;
    }
    // if echo, echo it out
    if ($isEcho) {
        //https://www.php.net/manual/en/function.htmlspecialchars.php
        echo htmlspecialchars($returnValue, ENT_QUOTES);
        //encodes the html so it does execute the HTML code, sanitize data 
    // else return 
    } else {
        //https://www.php.net/manual/en/function.htmlspecialchars.php
        return htmlspecialchars($returnValue, ENT_QUOTES);
    }
}
function safer_echo($v, $k = null, $default = "", $isEcho = true)
{
    return se($v, $k, $default, $isEcho);
}
