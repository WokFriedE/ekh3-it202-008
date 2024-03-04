<?php

// color is not required in this function -- default is info 
function flash($msg = "", $color = "info")
{
    $message = ["text" => $msg, "color" => $color];
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $message);
    } else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $message);
    }
} // this queues up messages in the session token

function getMessages()
{
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash']; //cache to var
        $_SESSION['flash'] = array(); // reset back to empty array
        // clears out messages already used 
        return $flashes; // return the flash messages --> only use when about to display
    }
    return array();
}
