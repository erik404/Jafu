<?php
namespace erik404;

error_reporting(E_ALL | E_STRICT);

require(__DIR__ . '/../src/erik404/Jafu.php');
require __DIR__ . '/../vendor/autoload.php';

/**
 * Overwrite move_uploaded_file because this function is safe and therefor gives an error with PHPUnit test.
 * Use the unsafe copy function for testing purpose.
 *
 * @param $filename
 * @param $destination
 * @return bool
 */
function move_uploaded_file($filename, $destination)
{
    return copy($filename, $destination);
}