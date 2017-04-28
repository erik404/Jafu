<?php

error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use erik404\Jafu;

echo '<pre>';
$Jafu = new Jafu();
$Jafu->setAllowedFileTypes(Jafu::IMAGE_TYPES);

if ($_FILES) {
    $Jafu->setFiles($_FILES);
    if (!$Jafu->save()){
        echo 'Errors: ';
        print_r($Jafu->getErrors());
    }
}


?>

<html>
<body>
<form action="#" enctype="multipart/form-data" method="post">
        <input type="file" name="poep" />
    <input type="file" name="kutje[]" multiple="multiple" />
        <input type="submit" value="Send">
</form>

</body>
</html>
