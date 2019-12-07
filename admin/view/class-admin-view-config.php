<?php

class Sumedia_Urlify_Admin_View_Config
{
    protected $data = [];

    public function set($urltype, $url)
    {
        $this->data[$urltype] = $url;
    }

    public function get($urltype)
    {
        return $this->data[$urltype];
    }
}