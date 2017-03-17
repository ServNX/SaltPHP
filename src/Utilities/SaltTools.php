<?php

namespace Salt\Utilities;

use Salt\Salt;
use Symfony\Component\Yaml\Yaml;

class SaltTools
{
    /**
     * @var $config
     */
    private $config;

    /**
     * @var Salt
     */
    private $salt;

    public function __construct(Salt $salt)
    {
        $this->salt = $salt;
        $this->config = $salt->config();
    }

    public function refreshPillars()
    {
        $this->salt->execute(hostname(), 'saltutil.refresh_pillar');
        return $this;
    }

    public function getServices($target, $search = '')
    {
        $results = trim(shell_exec("salt '$target' service.get_all | grep \"$search\""));

        if (str_contains($results, "\n")) {
            $results = explode("\n", $results);
        }

        if (is_array($results)) {
            foreach ($results as $key => $service) {
                $results[$key] = trim(str_replace('- ', '', $service));
            }

            array_splice($results, 0, 1);

        } else {
            $results = [str_replace('- ', '', $results)];
        }

        return $results;
    }
}