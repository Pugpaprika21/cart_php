<?php

interface ContainerInterface
{
    /**
     * @param string $container
     * @param callable $fn
     * @return void
     */
    public function set($container, $fn);

    /**
     * @param string $container
     * @return mixed
     */
    public function get($container);
}