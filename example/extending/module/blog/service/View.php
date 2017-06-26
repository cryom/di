<?php

namespace vivace\di\example\extending\module\blog\service;


use vivace\di\example\extending\service\ViewInterface;

class View implements ViewInterface
{
    private $tpl;

    public function __construct($tpl)
    {
        $this->tpl = $tpl;
    }

    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @return mixed
     */
    public function getTpl()
    {
        return $this->tpl;
    }
}