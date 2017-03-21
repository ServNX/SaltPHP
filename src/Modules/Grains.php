<?php

namespace Salt\Modules;

class Grains extends BaseModule
{
    public function items($target = '*') {
        $this->salt->execute('grains.items', $target);
        return $this->getResults();
    }

    public function item($item, $target = '*') {
        $this->salt->execute('grains.item', $target, [$item]);
        return $this->get($item);
    }
}