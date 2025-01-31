<?php
namespace highlanddev\rapiddownload\variables;

use Craft;
use craft\elements\Asset;
use craft\base\Field;

use highlanddev\rapiddownload\fields\RapidDownloadField;

class RapidDownloadVariable
{
    // RapidDownloadVariable.php
    public function renderDownloadForm($field, Field $fieldDefinition, $options = []): string
    {
        if (!$field) {
            return '';
        }

        $assets = $field->all();

        // Get the current view component
        $view = Craft::$app->getView();

        // Store the current template mode
        $oldMode = $view->getTemplateMode();

        // Switch to plugin templates mode
        $view->setTemplateMode($view::TEMPLATE_MODE_CP);

        try {
            // Get plugin path
            $pluginHandle = 'rapid-download';
            $template = 'frontend/_download-form';

            $html = $view->renderTemplate(
                $pluginHandle . '/' . $template,
                [
                    'assets' => $assets,
                    'fieldSettings' => $fieldDefinition,
                    'options' => $options
                ]
            );
        } finally {
            // Restore the original template mode
            $view->setTemplateMode($oldMode);
        }

        return $html;
    }
}