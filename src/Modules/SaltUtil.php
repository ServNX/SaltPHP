<?php

namespace Salt\Modules;

class SaltUtil extends BaseModule
{
    public function refresh_pillars($target = '*')
    {
        $this->salt->execute('saltutil.refresh_pillar', $target);
        return $this->getResults();
    }

}