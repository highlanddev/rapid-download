<?php
namespace highlanddev\rapiddownload\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Assets;

class RapidDownloadField extends Assets
{
    // Keep just the custom text options
    public bool $allowDirectDownload = false;
    public string $formTitle = 'Download Files';
    public string $formDescription = 'Enter your email address to receive a download link for the following files:';
    public string $buttonText = 'Send';
    public string $successMessage = 'Check your email for the download links!';
    public bool $enablePerEntrySettings = false;

    public static function displayName(): string
    {
        return 'Rapid Download Files';
    }

    public function getSettingsHtml(): string
    {
        // Get parent's settings HTML first
        $parentHtml = parent::getSettingsHtml();

        // Add our custom settings
        $customHtml = Craft::$app->getView()->renderTemplate('rapid-download/downloads/_field-settings', [
            'field' => $this,
        ]);

        return $parentHtml . $customHtml;
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $assetHtml = parent::getInputHtml($value, $element);

        if ($this->enablePerEntrySettings) {
            return $assetHtml . Craft::$app->getView()->renderTemplate(
                    'rapid-download/downloads/_entry-settings',
                    [
                        'field' => $this,
                        'element' => $element,
                    ]
                );
        }

        return $assetHtml;
    }
}