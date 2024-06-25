<?php

$host = "db";
$dbname = "exampledb";
$user = "exampleuser";
$password = "examplepass";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("connection failed: " . $e->getMessage());
}
