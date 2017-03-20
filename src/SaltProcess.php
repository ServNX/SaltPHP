<?php

namespace Salt\Utilities;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

class SaltProcess
{
    /**
     * @var string $failureLevel
     */
    protected $failureLevel = 'exception';

    public function __construct()
    {

    }

    /**
     * Executes a new Process instance
     *
     * @param string $cmd
     */
    public function execute($cmd)
    {

        $process = new SymfonyProcess($cmd);

        $this->processStart($process);

        if (!$process->isSuccessful()) {
            $method = $this->failureLevel;
            $this->$method($process);
        }

        $this->processStop($process);

        echo "\n\n";
        return $process->getOutput();
    }

    /**
     * @param $process
     */
    protected function processStart(SymfonyProcess $process)
    {
        $process->setTimeout(3600);
        $process->run();
    }

    /**
     * @param $process
     */
    protected function processStop(SymfonyProcess $process)
    {
        $process->stop();
    }

    /**
     * @return string
     */
    public function getFailureLevel()
    {
        return $this->failureLevel;
    }

    /**
     * @param string $failureLevel
     */
    public function setFailureLevel($failureLevel)
    {
        $this->failureLevel = $failureLevel;
        return $this;
    }

    /**
     * @param $process
     */
    protected function exception(SymfonyProcess $process)
    {
        throw new ProcessFailedException($process);
    }

    /**
     * @param $process
     */
    protected function log(SymfonyProcess $process)
    {
        throw new NotImplementedException('The log failureLevel has not yet been implemented!');
    }

    /**
     * @param $process
     */
    protected function report(SymfonyProcess $process)
    {
        return $process->getOutput();
    }
}