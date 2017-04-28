<?php

require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use erik404\Jafu;

echo '<pre>';
if ($_FILES) {
    $jafu = new Jafu();


    $jafu->setFile($_FILES['datafile']['tmp_name']);
    $jafu->assertMimeType();
}



?>

<html>
<body>
<form action="#" enctype="multipart/form-data" method="post">
        <input type="file" name="datafile" id="tietjes" size="40">
        <input type="submit" value="Send">
</form>

</body>
</html>
