<?php

namespace Salt;

use Salt\Contracts\SaltInterface;

use Salt\Utilities\SaltProcess;

class Salt implements SaltInterface
{

    // todo: this class needs some abstraction

    /**
     * Container for the results
     *
     * @var array $results
     */
    protected $results;

    /**
     * The formatted pillar data to be sent
     *
     * @var string $data to be formatted as python dictionary
     */
    protected $data = '';

    /**
     * @var SaltConfig
     */
    private $config;

    /**
     * Salt constructor.
     */
    public function __construct(SaltConfig $config = null)
    {
        $this->config = $config;

        if ($config == null) {
            $this->config = new SaltConfig();
        }
    }

    public function config()
    {
        return $this->config;
    }

    /**
     * Executes the given salt module with options
     *
     * @param string $target
     * @param string $module
     * @param array $args
     * @param array $data
     * @param string $out
     * @param string $append
     * @return array|mixed
     */
    public function execute($target, $module, $args = [], $data = [], $out = 'json', $append = '')
    {
        $this->target = $target;

        $cmd = "$module";

        if (!empty($args)) {
            $args = implode(' ', $args);
            $cmd .= " $args";
        }

        if (!empty($data)) {
            $this->data($data);
            $cmd .= " $this->data";
        }

        $cmd .= " --out=$out";

        if ($append != '') {
            $cmd .= " $append";
        }

        $output = $this->cmd($cmd, $target);

        $this->results = json_decode($output, true);

        $this->validateResults();

        // reset some properties
        $this->clean();

        return $this->results;
    }

    /**
     * Executes a command as a sub process
     *
     * @param $cmd
     * @return string
     */
    public function cmd($cmd, $target = '*')
    {
        return (new SaltProcess())->execute("salt '$target' " . $cmd);
    }

    /**
     * Formats pillar data to be sent through cli
     *
     * @param array $data
     * @return string
     * @throws \Exception
     */
    protected function data($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        $built = "pillar='$json'";

        if (!is_array($data) || empty($data)) {
            throw new \Exception("Data must be an array containing keys and values!");
        }

        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new \Exception("Data must contain key AND value pairs!");
            }
        }

        return $this->data = $built;
    }

    /**
     * todo: move into SaltTools
     * Pings the target
     *
     * @param $target
     * @return mixed
     */
    public function ping($target)
    {
        $this->execute($target, 'test.ping', []);
        return isset($this->results[$target]) ? $this->results[$target] : $this->results;
    }

    // todo: move into SaltTools
    public function getKeys($list = 'all')
    {
        $process = trim(shell_exec("salt-key -L --out=json"));

        $keys = json_decode($process, true);

        if ($list != 'all') {
            return $keys[$list];
        }

        return $keys;
    }

    // todo: move into SaltTools
    public function acceptMinionKey($target)
    {
        $minions = $this->getKeys('minions');

        if (in_array($target, $minions)) {
            return true;
        }

        $minions_pre = $this->getKeys('minions_pre');

        if (in_array($target, $minions_pre)) {
            (new SaltProcess())->execute("salt-key -y -a '$target'");
        }

        return false;
    }

    /**
     * Returns an array of values of the given key from the stored results.
     *
     * @param $key
     * @return mixed
     */
    public function getKeyValueFromResults($key, $target = '*')
    {
        $values = [];

        if ($target == '*') {
            foreach ($this->results as $target) {
                if (is_array($target)) {
                    foreach ($target as $command => $value) {
                        array_push($values, $value[$key]);
                    }
                } else {
                    array_push($values, $target);
                }
            }
        } else {
            if (is_array($this->results[$target])) {
                foreach ($this->results[$target] as $command) {
                    if (is_array($command)) {
                        array_push($values, $command[$key]);
                    } else {
                        array_push($values, $command);
                    }
                }
            } else {
                array_push($values, $this->results[$target]);
            }
        }


        return $values;
    }

    protected function validateResults()
    {

    }


    /**
     * Resets properties
     */
    public function clean()
    {
        $this->data = '';
        $this->target = null;
    }
}