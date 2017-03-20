<?php

namespace Salt\Modules;

use Salt\Contracts\SaltInterface;

class Service
{
    /**
     * @var SaltInterface
     */
    private $salt;

    public function __construct(SaltInterface $salt)
    {
        $this->salt = $salt;
    }

    public function all($target = '*')
    {
        return $this->parse($this->salt->cmd("service.get_all", $target));
    }

    public function get($search, $target = '*')
    {
        //dd($this->salt->cmd("service.get_all | grep \"$search\"", $target));
        return $this->parse($this->salt->cmd("service.get_all | grep \"$search\"", $target));
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