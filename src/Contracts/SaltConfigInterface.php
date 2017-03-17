<?php

namespace Salt\Contracts;

interface SaltConfigInterface
{
    public function get($path, $default = null);

    public function set($path, $value);

    public function add($path, array $values);

    public function have($path);

    public function setValues($values);

    public function getValues();

    public function search($searchKey, $array = null);

    public function getDirectory();

    public function getPillar($path, $key = '');

    public function setPillar($path, $key, $value);

    public function getDelimiter();

    public function setDelimiter($delimiter);
}