<?php

namespace Salt;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Salt\Contracts\SaltConfigInterface;
use Salt\Policies\AddPillarPolicy;
use Salt\Utilities\SaltTools;
use Symfony\Component\Yaml\Yaml;

class SaltConfig implements SaltConfigInterface
{

    /**
     * @var string
     */
    protected $delimiter = '::';

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var array
     */
    protected $directory;


    /**
     * SaltConfig constructor.
     * @param string|null $directory
     * @throws \Exception
     */
    public function __construct($directory = null)
    {
        if ($directory !== null) {
            $this->directory = $directory;
            $this->addDirectory($directory);
        }

        $this->buildDefaults();
    }

    /**
     * @return array
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $path
     * @param string $default
     * @return mixed
     */
    public function get($path, $default = null)
    {
        $array = $this->values;

        if (!empty($path)) {
            $keys = explode($this->delimiter, $path);
            foreach ($keys as $key) {
                if (isset($array[$key])) {
                    $array = $array[$key];
                } else {
                    return $default;
                }
            }
        }

        return $array;
    }

    /**
     * @param string $path
     * @param mixed $value
     */
    public function set($path, $value)
    {
        if (!empty($path)) {
            $at = &$this->values;
            $keys = explode($this->delimiter, $path);

            while (count($keys) > 0) {
                if (count($keys) === 1) {
                    if (is_array($at)) {
                        $at[array_shift($keys)] = $value;
                    } else {
                        throw new \RuntimeException("Can not set value at this path ($path) because is not array.");
                    }
                } else {
                    $key = array_shift($keys);

                    if (!isset($at[$key])) {
                        $at[$key] = array();
                    }

                    $at = &$at[$key];
                }
            }
        } else {
            $this->values = $value;
        }
    }

    /**
     * @param $path
     * @param array $values
     */
    public function add($path, array $values)
    {
        $get = (array)$this->get($path);
        $this->set($path, $this->arrayMergeRecursiveDistinct($get, $values));
    }

    /**
     * @param string $path
     * @return bool
     */
    public function have($path)
    {
        $keys = explode($this->delimiter, $path);
        $array = $this->values;
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @param array $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    public function getPillar($path, $key = '')
    {
        $delim = $this->delimiter;

        $pillar = $path;

        if (str_contains($pillar, $delim)) {
            $pillar = explode($delim, $path)[0];
            $path = $pillar . $delim . $path;
        }

        $conf = $this->get('pillar' . $delim . $path);

        if ($key == '') {
            return $conf;
        }

        return $this->search($key, $conf)['value'];
    }

    public function setPillar($path, $key, $value)
    {
        $delim = $this->delimiter;

        $pillar = $path;

        if (str_contains($path, $delim)) {
            $pillar = explode($delim, $path)[0];
            $conf = $this->get('pillar' . $delim . $pillar . $delim . $path);
            $path = 'pillar' . $delim . $pillar . $delim . $path . $delim . $this->search($key, $conf)['path'];
        } else {
            $conf = $this->get('pillar' . $delim . $pillar);
            $path = 'pillar' . $delim . $pillar . $delim . $this->search($key, $conf)['path'];
        }

        $this->set($path, $value);

        return $this->getPillar($pillar);
    }

    public function addPillars($directories = null)
    {
        // todo: re-thinking that we may be able to take advantage of the salt command 'pillar.items'

        foreach ($directories as $dir) {

            // check policy
            (new AddPillarPolicy($this))->allows($dir);

            $pillars = $this->pillarToArray("$dir/top.sls")['base']['*'];

            foreach ($pillars as $pillar) {

                if (is_dir("$dir/$pillar")) {

                    if (!file_exists("$dir/$pillar/init.sls")) {
                        throw new \Exception("$dir/$pillar/init.sls does not exist!");
                    }

                    $this->add('pillar', [
                        $pillar => $this->pillarToArray(
                            "$dir/$pillar/init.sls"
                        )
                    ]);

                } elseif (is_file("$dir/$pillar.sls")) {

                    $this->add('pillar', [
                        $pillar => $this->pillarToArray(
                            "$dir/$pillar.sls"
                        )
                    ]);

                } else {
                    throw new \Exception("addPillars failed for unknown reasons!");
                }
            }
        }
    }

    public function pillarToArray($file)
    {
        if (!file_exists($file)) {
            throw new \Exception($file . ' does NOT exist');
        }

        return Yaml::parse(file_get_contents($file));
    }

    /**
     * Searches the configuration (optionally pass in an array to search)
     *
     * @param $searchKey
     * @param null $array
     * @return array|bool
     *
     * Returns array with the [path] and [value] of the $searchKey
     */
    public function search($searchKey, $array = null)
    {
        $delim = $this->delimiter;

        if ($array == null) {
            $array = $this->values;
        }

        //create a recursive iterator to loop over the array recursively
        $iter = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($array),
            RecursiveIteratorIterator::SELF_FIRST);

        //loop over the iterator
        foreach ($iter as $key => $value) {
            //if the key matches our search
            if ($key === $searchKey) {
                //add the current key
                $keys = array($key);
                //loop up the recursive chain
                for ($i = $iter->getDepth() - 1; $i >= 0; $i--) {
                    //add each parent key
                    array_unshift($keys, $iter->getSubIterator($i)->key());
                }
                //return our output array
                return array('path' => implode($delim, $keys), 'value' => $value);
            }
        }

        //return false if not found
        return false;
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * arrayMergeRecursiveDistinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * arrayMergeRecursiveDistinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * If key is integer, it will be merged like array_merge do:
     * arrayMergeRecursiveDistinct(array(0 => 'org value'), array(0 => 'new value'));
     *     => array(0 => 'org value', 1 => 'new value');
     *
     * @param array $array1
     * @param array $array2
     * @return array
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     * @author Anton Medvedev <anton (at) elfet (dot) ru>
     */
    protected function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged[$key]) && is_array($merged[$key])) {
                if (is_int($key)) {
                    $merged[] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                } else {
                    $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
                }
            } else {
                if (is_int($key)) {
                    $merged[] = $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    protected function addDirectory($directory)
    {
        if (!is_dir($directory)) {
            throw new \Exception('Invalid directory ' . $directory . ' Use absolute path!');
        }

        $files = scandir($directory);

        if (array_search('salt.php', $files) !== false or array_search('pillar.php', $files) !== false) {
            throw new \Exception('salt.php and pillar.php are reserved for default configuration');
        }

        foreach ($files as $file) {
            if (is_file("$directory/$file")) {
                $conf = require $directory . "/SaltConfig.php";
                $this->values[basename($file, '.php')] = $conf;
            }
        }
    }

    protected function buildDefaults()
    {
        $directory = __DIR__ . '/Configs';

        $files = scandir($directory);

        foreach ($files as $file) {
            if (is_file("$directory/$file")) {
                $conf = require $directory . "/SaltConfig.php";
                $this->values[basename($file, '.php')] = $conf;
            }
        }
    }

}