<?php

class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $building = [];

    /**
     * @param string $container
     * @param callable $fn
     * @return void
     */
    public function set($container, $fn)
    {
        $this->building[$container] = $fn->bindTo($this, $this);
    }

    /**
     * @param string $container
     * @return mixed
     */
    public function get($container)
    {
        if (isset($this->building[$container])) {
            return ($this->building[$container])();
        }
        throw new Exception("container not found: {$container}");
    }
}
