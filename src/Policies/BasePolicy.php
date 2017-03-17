<?php

namespace Salt\Policies;

use Salt\SaltConfig;

abstract class BasePolicy
{

    /**
     * @var SaltConfig
     */
    protected $config;

    public function __construct(SaltConfig $config)
    {
        $this->config = $config;
    }

    abstract public function allows($param = null);
}