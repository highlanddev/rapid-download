<?php
namespace highlanddev\rapiddownload\variables;

use Craft;
use craft\elements\Asset;
use craft\base\Field;

use highlanddev\rapiddownload\fields\RapidDownloadField;

class RapidDownloadVariable
{
    public function renderDownloadForm($field, Field $fieldDefinition, $options = []): string
    {
        if (!$field) {
            return '';
        }

        $assets = $field->all();

        // Get the entry ID
        $entryId = null;
        if (!empty($assets)) {
            // Get the ownerId (entryId) from the first asset, if possible
            $entryId = $assets[0]->ownerId ?? null;
        }

        // Get the current view component
        $view = Craft::$app->getView();

        // Store the current template mode
        $oldMode = $view->getTemplateMode();

        // Ensure default field settings are an array
        $defaultSettings = [];
        if ($fieldDefinition instanceof RapidDownloadField && $entryId) {
            $defaultSettings = $fieldDefinition->getEntrySettings($entryId) ?? []; // Always return an array
        }

        $mergedSettings = array_merge($defaultSettings, $options); // Merge custom overrides

        // Switch to plugin templates mode
        $view->setTemplateMode($view::TEMPLATE_MODE_CP);

        try {
            // Get plugin path
            $pluginHandle = 'rapid-download';
            $template = 'frontend/_download-form';

            // Render HTML with merged settings
            $html = $view->renderTemplate(
                $pluginHandle . '/' . $template,
                [
                    'assets' => $assets,
                    'fieldSettings' => $mergedSettings, // Use merged settings
                ]
            );
        } finally {
            // Restore the original template mode
            $view->setTemplateMode($oldMode);
        }

        return $html;
    }
}