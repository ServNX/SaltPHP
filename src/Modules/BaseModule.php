<?php

namespace Salt\Modules;

use Salt\Contracts\SaltInterface;

abstract class BaseModule
{
    /**
     * @var SaltInterface
     */
    protected $salt;

    public function __construct(SaltInterface $salt)
    {
        $this->salt = $salt;
    }

    public function get($searchkey) {
        return $this->salt->searchResults($searchkey)[0];
    }

    public function getResults() {
        return $this->salt->getResults();
    }
}