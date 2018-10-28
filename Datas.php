<?php
/**
 * Created by PhpStorm.
 * User: hakanbaysal
 * Date: 28.10.2018
 * Time: 02:06
 */

class Datas
{
    public $param_id;
    public $result;

    public function getParamId()
    {
        return $this->param_id;
    }

    public function setParamId($param_id)
    {
        $this->param_id = $param_id;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }
}