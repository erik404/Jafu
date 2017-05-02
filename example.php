<html>
<head>
    <title>Jafu</title>
</head>
<body>
<form action="#" enctype="multipart/form-data" method="post">
    <label for="foo">Single upload: </label><input type="file" name="foo"/>
    <label for="bar">Multiple uploads: </label><input type="file" name="bar[]" multiple="multiple"/>
    <input type="submit" value="Send">
</form>
<pre>
<?php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use erik404\Jafu;

// check if the $_FILES global holds any information
if (!empty($_FILES)) {

    // instantiate Jafu
    $jafu = new Jafu();

    // set the allowed MIME types. (expects at least 1 one-dimensional array but you can pass as many as you please)
    $jafu->setAllowedMimeTypes(Jafu::IMAGE_TYPES, array('other/types', 'you/need'));

    // set the $_FILES global to Jafu
    $jafu->setFiles($_FILES);

    // save the files, the save method returns a success boolean
    $success = $jafu->save();

    // check if the save was successful
    if ($success === true) {
        // get the result array
        $results = $jafu->getResult();
        // loop through the results holding the file
        foreach ($results as $result) {

            // Example result
            // Array
            //  (
            //      [file] => Jafu\src\erik404\14937462385908c23e06d33195654509_example.PNG // the file as how it exists on the filesystem
            //      [inputName] => bar // the input element from where the file originates from
            //  )
            //  ...
            //  ...

            // output to screen
            echo '<p>File: "' . $result['file'] . '" uploaded via input element "' . $result['inputName'] . '"</p>';
        }
    } else {
        // get the errors array from Jafu
        $errors = $jafu->getErrors();
        // loop through the errors
        foreach ($errors as $error) {

            // Example result
            // Array
            //  (
            //      [name] => John Cage - 4'33".wav // the name of the file which has an error
            //      [inputName] => foo // the input element from where the file originates from
            //      [error] => 9 // the error code
            //      [message] => File type text/plain not allowed. // the error message
            //  )
            //  -- OR
            //  Array
            //  (
            //      [name] =>
            //      [inputName] =>
            //      [error] => 4
            //      [message] => No file was uploaded.
            //  )
            //  ...
            //  ...

            // output to screen
            echo '<p>Failed with error code "' . $error['error'] . '" and message "' . $error['message'] . '"</p>';
        }
    }
}
?>
</pre>
</body>
</html>
