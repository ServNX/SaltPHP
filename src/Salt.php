<?php

namespace Salt;

use Salt\Contracts\SaltInterface;

use Salt\Utilities\Process;
use Salt\Utilities\SaltTools;

class Salt implements SaltInterface
{

    // todo: this class needs some abstraction
    // todo: maybe this class is strictly a gateway class ?

    /**
     * Container for the results
     *
     * @var array $results
     */
    protected $results;

    /**
     * The Target
     *
     * @var string $target
     */
    protected $target;

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
     * @var SaltTools $tools
     */
    private $tools;

    /**
     * Salt constructor.
     */
    public function __construct(SaltConfig $config = null)
    {
        $this->config = $config;

        if ($config == null) {
            $this->config = new SaltConfig();
        }

        $this->tools = new SaltTools($this);
    }

    public function config()
    {
        return $this->config;
    }

    public function tools()
    {
        return $this->tools;
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

        $cmd = "salt '$target' $module";

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

        $output = $this->cmd($cmd);

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
    protected function cmd($cmd)
    {
        return (new Process())->execute($cmd);
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
    public function osFamily($target)
    {
        $this->execute($target, 'grains.item', ['os_family']);
        return isset($this->results[$target]) ? $this->results[$target]['os_family'] : '';
    }

    // todo: move into SaltTools
    public function os($target)
    {
        $this->execute($target, 'grains.item', ['os']);
        return isset($this->results[$target]) ? $this->results[$target]['os'] : '';
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
            (new Process())->execute("salt-key -y -a '$target'");
        }

        return false;
    }

    /**
     * Returns the given key from the results.
     *
     * @param $key
     * @return mixed
     */
    public function getResults($key = null)
    {
        $values = [];

        if ($this->target == '*') {
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
            if (is_array($this->results[$this->target])) {
                foreach ($this->results[$this->target] as $command) {
                    if (is_array($command)) {
                        array_push($values, $command[$key]);
                    } else {
                        array_push($values, $command);
                    }
                }
            } else {
                array_push($values, $this->results[$this->target]);
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