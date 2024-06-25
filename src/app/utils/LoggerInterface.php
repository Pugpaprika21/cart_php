<?php

interface LoggerInterface
{
    /**
     * @param string $content
     * @return void
     */
    public function log(string $content);
}
