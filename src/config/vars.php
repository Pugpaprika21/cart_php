<?php

define("ARR_UPPER_CASE", false);

// --------------- db ------------ //

define("DB_DRIVER", "pgsql");
define("DB_HOST", "db");
define("DB_USER", "exampleuser");
define("DB_PASS", "examplepass");
define("DB_NAME", "exampledb");

// --------------- url ------------ //

define("URL_SCHEME", $_SERVER["REQUEST_SCHEME"] . "://" .  $_SERVER["SERVER_NAME"] . ":"  . $_SERVER["SERVER_PORT"] . "/");