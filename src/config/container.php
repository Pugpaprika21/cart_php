<?php

$container = null;

$container = new Container();

$container->set("config", function (): array {
    return [
        "db" => [
            "driver" => DB_DRIVER,
            "host" => DB_HOST,
            "dbname" => DB_NAME,
            "user" => DB_USER,
            "password" => DB_PASS,
        ]
    ];
});

$container->set("db", function (): ?PDO {
    try {
        $config = $this->get("config");
        
        $pdo = new PDO("{$config["db"]["driver"]}:host={$config["db"]["host"]};dbname={$config["db"]["dbname"]}", $config["db"]["user"], $config["db"]["password"]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("connection failed: " . $e->getMessage());
    }
});