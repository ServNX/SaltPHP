<?php

namespace Salt\Contracts;

interface SaltInterface
{
    /**
     * @return mixed
     */
    public function config();

    /**
     * Executes the given module with given params and given data
     *
     * @param string $target
     * @param string $module
     * @param array $args
     * @param array $data
     * @return array|mixed
     */
    public function execute($target, $module, $args = [], $data = [], $out = 'json', $append = '');

    /**
     * @param $cmd
     * @param string $target
     * @return mixed
     */
    public function cmd($cmd, $target = '*');

    /**
     * Pings the target
     *
     * @param $target
     * @return mixed
     */
    public function ping($target);

    /**
     * @param string $list
     * @return mixed
     */
    public function getKeys($list = 'all');

    /**
     * @param $target
     * @return mixed
     */
    public function acceptMinionKey($target);

    /**
     * Returns an array of values of the given key from the stored results.
     *
     * @param $key
     * @return mixed
     */
    public function getKeyValueFromResults($key);

    /**
     * Resets properties
     */
    public function clean();
}