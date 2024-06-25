<?php

include_once "app/libs/rb.php";
R::setup("" . DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . "", DB_USER, DB_PASS);
R::debug(false);

R::ext("xdispense", function ($type) {
    return R::getRedBean()->dispense($type);
});
