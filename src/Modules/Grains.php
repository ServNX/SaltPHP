<?php

namespace Salt\Modules;

use Salt\Contracts\SaltInterface;

class Grains
{
    /**
     * @var SaltInterface
     */
    private $salt;

    public function __construct(SaltInterface $salt)
    {
        $this->salt = $salt;
    }

    public function all($target = '*') {
        return $this->salt->execute($target, 'grains.items');
    }

    public function os($target = '*')
    {
        $results = $this->salt->execute($target, 'grains.item', ['os']);
        return isset($results[$target]) ? $results[$target]['os'] : '';
    }

    public function os_family($target = '*')
    {
        $results = $this->salt->execute($target, 'grains.item', ['os_family']);
        return isset($results[$target]) ? $results[$target]['os_family'] : '';
    }
}