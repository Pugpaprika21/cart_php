<?php

class ProductLogger implements LoggerInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function log(string $content)
    {
        $baseURL = current_url();

        $message = "[" . date("Y-m-d H:i:s") . "] " . $_SERVER["REQUEST_METHOD"] . " " . $baseURL . " " . $content . PHP_EOL;
        file_put_contents($this->path, $message, FILE_APPEND | LOCK_EX);
    }
}
