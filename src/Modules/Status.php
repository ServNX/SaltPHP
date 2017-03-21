<?php

namespace Salt\Modules;

class Status extends BaseModule
{
    public function cpuinfo($target = '*', $searchkey = null)
    {
        $this->salt->execute('status.cpuinfo', $target);
        return ($searchkey !== null) ? $this->get($searchkey) : $this->getResults();
    }

    public function loadavg($target = '*', $searchkey = null)
    {
        $this->salt->execute('status.loadavg', $target);
        return ($searchkey !== null) ? $this->get($searchkey) : $this->getResults();
    }
}

