<?php

set_error_handler('error_handler');

function error_handler($severity, $message, $filename, $lineno ) {
    if (error_reporting() == 0) {
        return;
    }
    echo '<pre>';
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
//    exit();
}

