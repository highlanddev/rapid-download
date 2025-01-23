<?php
namespace highlanddev\rapiddownload;


class RapidDownload extends \craft\base\Plugin
{
    public static $plugin;

    public function init()
    {
        parent::init();
        self::$plugin = $this;
    }
}
