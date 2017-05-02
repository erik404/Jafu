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
    // you can use predefined constants from the class. IMAGE_TYPES, APPLICATION_TYPES, AUDIO_TYPES, TEXT_TYPES, VIDEO_TYPES.
    $jafu->setAllowedMimeTypes(Jafu::IMAGE_TYPES, array('other/types', 'you/need'));

    // the default save location is stored in the config.php file under 'defaultSaveLocation' and can be changed.
    // you can override the save location using the setSaveLocation function and passing a location on the file-system.
    // you can always retrieve the 'defaultSaveLocation' stored in the config with the getDefaultSaveLocation function.
    $jafu->setSaveLocation($jafu->getDefaultSaveLocation()); // $jafu->setSaveLocation('/path/on/system');

    // pass the $_FILES to Jafu
    $jafu->setFiles($_FILES);

    // save the files, the save method returns a success boolean
    $success = $jafu->save();

    // check if the save was successful
    if ($success === true) {
        // get the result array
        $results = $jafu->getResults();
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
            //  ...
            //  ...
            //  -- OR
            //  Array
            //  (
            //      [name] =>
            //      [inputName] =>
            //      [error] => 4
            //      [message] => No file was uploaded.
            //  )

            // output to screen
            echo '<p>Failed with error code "' . $error['error'] . '" and message "' . $error['message'] . '"</p>';
        }
    }
}
?>
</pre>
</body>
</html>
