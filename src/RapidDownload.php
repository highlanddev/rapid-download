<?php
namespace highlanddev\rapiddownload;

use highlanddev\rapiddownload\fields\RapidDownloadField;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use yii\base\Event;

class RapidDownload extends \craft\base\Plugin
{
    public static $plugin;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = RapidDownloadField::class;
            }
        );
    }
}