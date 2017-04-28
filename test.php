<?php

error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use erik404\Jafu;

echo '<pre>';
$Jafu = new Jafu();
$Jafu->setAllowedFileTypes(Jafu::IMAGE_TYPES);

if ($_FILES) {
    $Jafu->setFiles($_FILES);

}


?>

<html>
<body>
<form action="#" enctype="multipart/form-data" method="post">
        <input type="file" name="poep" />
    <input type="file" name="kutje[]" multiple="multiple" />
        <input type="submit" value="Send">
</form>

<br />
<br />
<pre>
    SINGLE FILE UPLOAD
    Array
(
    [datafile] => Array
        (
            [name] => Screen Shot 2017-04-05 at 13.46.31.png
            [type] => image/png
            [tmp_name] => /Applications/MAMP/tmp/php/phpd6vvkK
            [error] => 0
            [size] => 238452
        )

)


    MULTIPLE FILE UPLOAD
    Array
(
    [datafile] => Array
        (
            [name] => Array
                (
                    [0] => Screen Shot 2017-04-05 at 13.46.25.png
                    [1] => Screen Shot 2017-04-05 at 13.46.31.png
                    [2] => Screen Shot 2017-04-05 at 14.56.11.png
                )

            [type] => Array
                (
                    [0] => image/png
                    [1] => image/png
                    [2] => image/png
                )

            [tmp_name] => Array
                (
                    [0] => /Applications/MAMP/tmp/php/phpI5gIqQ
                    [1] => /Applications/MAMP/tmp/php/phpu7mhic
                    [2] => /Applications/MAMP/tmp/php/phpa1lTOk
                )

            [error] => Array
                (
                    [0] => 0
                    [1] => 0
                    [2] => 0
                )

            [size] => Array
                (
                    [0] => 82314
                    [1] => 238452
                    [2] => 64001
                )

        )

)
</pre>

</body>
</html>
