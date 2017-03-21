<?php

namespace Salt\Modules;

class Service extends BaseModule
{
    public function all($target = '*')
    {
        return $this->parse($this->salt->cmd("service.get_all", $target));
    }

    public function get($searchkey, $target = '*')
    {
        return $this->parse($this->salt->cmd("service.get_all | grep \"$searchkey\"", $target));
    }

    private function parse($results)
    {
        if (str_contains($results, "\n")) {
            $results = explode("\n", $results);
        }

        if (is_array($results)) {
            foreach ($results as $key => $service) {
                $results[$key] = trim(str_replace('- ', '', $service));
            }

            array_splice($results, 1, 1);

        } else {
            $results = [str_replace('- ', '', $results)];
        }

        return $results;
    }
}