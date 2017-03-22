<?php

namespace Salt\Modules;

class Status extends BaseModule
{
    public function cpuinfo($target = '*', $searchkey = null)
    {
        $this->salt->execute('status.cpuinfo', $target);

        return $this->checkArgumentsAndGetResults($target, $searchkey);

    }

    public function loadavg($target = '*', $searchkey = null)
    {
        $this->salt->execute('status.loadavg', $target);

        return $this->checkArgumentsAndGetResults($target, $searchkey);
    }

    public function meminfo($target = '*', $searchkey = null)
    {
        $this->salt->execute('status.meminfo', $target);

        return $this->checkArgumentsAndGetResults($target, $searchkey);
    }

    /**
     * @param $target
     * @param $searchkey
     * @return mixed
     */
    private function checkArgumentsAndGetResults($target, $searchkey)
    {
        if ($searchkey !== null) {
            return $this->get($searchkey);
        }

        if ($target == '*') {
            return $this->getResults();
        }

        return $this->getResults()[$target];
    }
}

