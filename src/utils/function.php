<?php

/**
 * @param string $input
 * @return string
 */
function conText($input)
{
    if (!is_string($input)) {
        throw new InvalidArgumentException("Input must be a string.");
    }

    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    return $input;
}

/**
 * @param array|object $input
 * @param int $case
 * @return array
 */
function arr_upr($input, $case = MB_CASE_TITLE)
{
    $convToCamel = function ($str) {
        return str_replace(" ", "", ucwords(str_replace("_", " ", $str)));
    };

    if (is_object($input)) {
        $input = json_decode(json_encode($input), true);
    }

    $newArray = array();
    foreach ($input as $key => $value) {
        if (is_array($value) || is_object($value)) {
            $newArray[$convToCamel($key)] = arr_upr($value, $case);
        } else {
            $newArray[$convToCamel($key)] = $value;
        }
    }
    return $newArray;
}

/**
 * @return string
 */
function current_url()
{
    return sprintf(
        "%s://%s/%s",
        isset($_SERVER["HTTPS"]) ? "https" : "http",
        $_SERVER["HTTP_HOST"],
        $_SERVER["REQUEST_URI"]
    );
}

/**
 * @return array
 */
function route_parse_urls()
{
    $route = isset($_GET["route"]) ? trim($_GET["route"], "/") : "";
    $parts = explode("/", conText($route));
    $parsedRoute = [
        "route" => isset($parts[0]) ? conText($parts[0]) : "",
        "controller" => isset($parts[1]) ? conText($parts[1]) : "",
        "method" => isset($parts[2]) ? conText($parts[2]) : ""
    ];

    return $parsedRoute;
}

/**
 * @return void
 */
function header_cors()
{
    $origin = array_key_exists("HTTP_ORIGIN", $_SERVER) ? $_SERVER["HTTP_ORIGIN"] : "";

    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

/**
 * @return void
 */
function header_xss()
{
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
}

/**
 * @param string $input_str
 * @param integer $length
 * @return string
 */
function rend_string($input_str = "", $length = 10)
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ{$input_str}";
    $charactersLength = strlen($characters);
    $randomString = "";
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * file_uploaded('../../upload/image/', [
 *      'name' => $file['user_profile']['name'],
 *      'tmp_name' => $file['user_profile']['tmp_name']
 * ]);
 * 
 * return file name
 *
 * @param string $path_upload
 * @param array $file
 * @return string|null 
 */
function file_uploaded($pathUpload, $file)
{
    if (!file_exists($pathUpload)) return null;

    $imageFileType = pathinfo($file["name"], PATHINFO_EXTENSION);

    $nameFile = explode(".", $file["name"]);
    $fileName = rend_string($nameFile[0]) . round(microtime(true) * 1000) . "." . $imageFileType;

    $fileTarget = "{$pathUpload}{$fileName}";
    move_uploaded_file($file["tmp_name"], $fileTarget);
    chmod($fileTarget, 0777);

    return $fileName;
}

/**
 * @param string|null $key
 * @return mixed
 */
function request($key = null)
{
    $request = file_get_contents("php://input");
    $rawData = json_decode($request, true);

    return !empty($rawData[$key]) ? conText($rawData[$key]) : null;
}

/**
 * @param string|null $key
 * @return mixed
 */
function post($key = null)
{
    return !empty($_POST[$key]) ? conText($_POST[$key]) : null;
}

/**
 * @param string|null $key
 * @return mixed
 */
function query($key = null)
{
    return !empty($_GET[$key]) ? conText($_GET[$key]) : null;
}

// -------------------------------------- load class ----------------------------------------- //

spl_autoload_register(
    function ($className) {
        $app_config_paths = include "config/app_path.php";
        $classParts = explode("\\", $className);
        $classFileName = end($classParts);
        foreach ($app_config_paths["app"] as $folder) {
            $classFilePath = $folder . $classFileName . ".php";
            if (file_exists($classFilePath)) {
                include_once $classFilePath;
                return;
            }
        }
    }
);
