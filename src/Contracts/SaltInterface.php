<?php

namespace Salt\Contracts;

interface SaltInterface
{
    /**
     * @return mixed
     */
    public function config();

    /**
     * @return mixed
     */
    public function tools();

    /**
     * Executes the given module with given params and given data
     *
     * @param string $target
     * @param string $module
     * @param array $args
     * @param array $data
     * @return array|mixed
     */
    public function execute($target, $module, $args = [], $data = []);

    /**
     * Pings the target
     *
     * @param $target
     * @return mixed
     */
    public function ping($target);

    /**
     * @param $target
     * @return mixed
     */
    public function osFamily($target);

    /**
     * @param $target
     * @return mixed
     */
    public function os($target);

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
     * Returns the given key from the results.
     *
     * @param $key
     * @return mixed
     */
    public function getResults($key = null);

    /**
     * Resets properties
     */
    public function clean();
}