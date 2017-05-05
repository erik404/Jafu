<?php
namespace erik404;

/*
 * Set error reporting to the level to which Es code must comply.
 */
error_reporting(E_ALL | E_STRICT);
/*
 * Determine the root, library, and tests directories of the framework
 * distribution.
 */
require(__DIR__ . '/../src/erik404/Jafu.php');
require __DIR__ . '/../vendor/autoload.php';

/**
 * Overwrite here php functions
 */
function move_uploaded_file($filename, $destination)
{
    //Copy file
    return copy($filename, $destination);
}