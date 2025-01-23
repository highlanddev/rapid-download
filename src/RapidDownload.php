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

    protected function createSettingsModel(): ?craft\base\Model
    {
        return new \craft\base\Model();
    }

    public function afterInstall(): void
    {
        parent::afterInstall();

        // Run install migration
        Craft::$app->db->createCommand()
            ->checkIntegrity(false)
            ->execute();

        $migration = new \highlanddev\rapiddownload\migrations\Install();
        $migration->up();

        Craft::$app->db->createCommand()
            ->checkIntegrity(true)
            ->execute();
    }
}