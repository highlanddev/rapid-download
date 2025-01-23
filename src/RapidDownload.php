<?php
namespace highlanddev\rapiddownload;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\Cp;
use yii\base\Event;

class RapidDownload extends Plugin
{
    public static $plugin;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['rapid-download/downloads'] = 'rapid-download/downloads/index';
            }
        );

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                Cp::class,
                Cp::EVENT_REGISTER_CP_NAV_ITEMS,
                function(RegisterCpNavItemsEvent $event) {
                    $event->navItems[] = [
                        'url' => 'rapid-download/downloads',
                        'label' => 'Downloads',
                        'icon' => '@app/icons/download'
                    ];
                }
            );
        }
    }

    protected function createSettingsModel(): ?Model
    {
        return new Model();
    }

    public function afterInstall(): void
    {
        parent::afterInstall();

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