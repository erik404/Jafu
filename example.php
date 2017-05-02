<html>
<head>
    <title>Jafu - "Just Another File Uploader"</title>
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

$jafu = new Jafu();
$jafu->setAllowedFileTypes(Jafu::IMAGE_TYPES);

if ($_FILES) {
    $jafu->setFiles($_FILES);
    if (!$jafu->save()) {
        foreach ($jafu->getErrors() as $error) {
            print_r($error);
        }
    } else {
        foreach ($jafu->getResult() as $result) {
            print_r($result);
        }
    }
}
?>
</pre>
</body>
</html>