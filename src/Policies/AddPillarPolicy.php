<?php

namespace Salt\Policies;

class AddPillarPolicy extends BasePolicy
{
    // todo: refactor with steps array

    public function allows($param = null)
    {
        if ($param == null) {
            throw new \Exception(
                "param cannot be null!
                
                Expecting param to be string (a directory path)."
            );
        }

        $this->checkDirectory($param);

        $this->checkTopFile($param);

        $this->checkFormat($param);
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    private function checkFormat($dir)
    {
        $conditions =
            is_array($this->config->pillarToArray("$dir/top.sls")) ||
            is_array($this->config->pillarToArray("$dir/top.sls")['base']) ||
            is_array($this->config->pillarToArray("$dir/top.sls")['base']['*']);

        if (!$conditions) {
            throw new \Exception(
                "$dir/top.sls is not configured correctly!
                
                Please be sure that the file is formatted like the example shown below.
                
                Example (YAML) :
                
                base:
                  '*':
                    - users
                    - mysql"
            );
        }
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    private function checkTopFile($dir)
    {
        if (!file_exists("$dir/top.sls")) {
            throw new \Exception(
                "$dir/top.sls does NOT exist
                
                Please be sure that $dir/top.sls exists and is properly configured."
            );
        }
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    private function checkDirectory($dir)
    {
        if (!is_dir($dir)) {
            throw new \Exception(
                "$dir is not a directory!
                
                Please be sure that the directory exists."
            );
        }
    }
}