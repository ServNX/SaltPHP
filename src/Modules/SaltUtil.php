<?php

namespace Salt\Modules;

use Salt\Contracts\SaltInterface;

class SaltUtil
{
    private $salt;

    public function __construct(SaltInterface $salt)
    {
        $this->salt = $salt;
    }

    public function refresh_pillars($target = '*')
    {
        return $this->salt->execute($target, 'saltutil.refresh_pillar');
    }

}