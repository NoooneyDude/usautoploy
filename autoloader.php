<?php

spl_autoload_register(function($className) {
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $file = "classes/$file.php";

    require $file;
});
