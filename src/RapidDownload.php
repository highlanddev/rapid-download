<?php
namespace highlanddev\rapiddownload;


use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\Cp;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use highlanddev\rapiddownload\fields\RapidDownloadField;
use highlanddev\rapiddownload\variables\RapidDownloadVariable;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use craft\web\twig\variables\CraftVariable;
use craft\elements\Entry;

use yii\base\Event;


class RapidDownload extends Plugin
{
    public static $plugin;
    public $handle = 'rapid-download';

    public function init()
    {
        parent::init();
        self::$plugin = $this;
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => 'Rapid Download',
                    'permissions' => [
                        'accessPlugin-rapid-download' => [
                            'label' => 'Access Rapid Download',
                        ],
                    ],
                ];
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['rapid-download/downloads'] = 'rapid-download/downloads/index';
            }
        );
        Event::on(Entry::class, Entry::EVENT_AFTER_DELETE, function(Event $event) {
            $entry = $event->sender;

            Craft::$app->getDb()->createCommand()
                ->delete('rapiddownload_settings', ['entryId' => $entry->id])
                ->execute();
        });

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e) {
                $variable = $e->sender;
                $variable->set('rapidDownload', RapidDownloadVariable::class);
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

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = RapidDownloadField::class;
            }
        );

    }

    public function registerPermissions(): array
    {
        $permissions = [];
        $permissions['accessPlugin-' . $this->id] = [
            'label' => Craft::t('rapid-download', 'Access Rapid Download Plugin'),
        ];

        return $permissions;
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