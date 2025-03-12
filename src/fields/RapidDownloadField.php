<?php
namespace highlanddev\rapiddownload\fields;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Assets;
use yii\db\Query;

class RapidDownloadField extends Assets
{
    public bool $allowDirectDownload = false;
    public string $formTitle = 'Download Files';
    public string $formDescription = 'Enter your email address to receive a download link for the following files:';
    public string $buttonText = 'Send';
    public string $successMessage = 'Check your email for the download links!';
    public bool $enablePerEntrySettings = false;

    public bool $hideEntryFormSettings = false;

    private ?array $settings = null;

    public static function displayName(): string
    {
        return 'Rapid Download Files';
    }

    /**
     * Fetch saved settings for a specific entry.
     */
    public function getEntrySettings(int $entryId): ?array
    {
        $result = (new \yii\db\Query())
            ->select([
                'allowDirectDownload',
                'formTitle',
                'formDescription',
                'buttonText',
                'successMessage',
            ])
            ->from('{{%rapiddownload_settings}}')
            ->where([
                'entryId' => $entryId,
                'fieldId' => $this->id,
            ])
            ->one();

        return $result === false ? null : $result;
    }

    /**
     * Settings HTML for the field settings in the UI.
     */
    public function getSettingsHtml(): string
    {
        return parent::getSettingsHtml() .
            Craft::$app->getView()->renderTemplate(
                'rapid-download/downloads/_field-settings',
                ['field' => $this]
            );
    }

    /**
     * HTML for the field input, including any custom settings.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Process the asset input HTML using the parent method
        $assetHtml = parent::getInputHtml($value, $element);

        // Fetch any custom settings
        $settings = [];
        if ($this->enablePerEntrySettings && $element && $element->id) {
            $settings = $this->getEntrySettings($element->id);
        }

        // Append the custom entry settings template if per-entry settings are enabled
        // and form settings are not hidden
        if ($this->enablePerEntrySettings && !$this->hideEntryFormSettings) {
            return $assetHtml . Craft::$app->getView()->renderTemplate(
                    'rapid-download/downloads/_entry-settings',
                    [
                        'field' => $this,
                        'element' => $element,
                        'settings' => $settings, // Pass the settings to the entry-specific settings template
                    ]
                );
        }
        return $assetHtml; // If no per-entry settings, return only the asset input
    }

    /**
     * Normalize the field value, including adding the custom settings for the entry.
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Keep the normalized value as expected by Craft for relational fields
        $normalizedValue = parent::normalizeValue($value, $element);

        // Load custom settings into a separate property for internal use, without modifying $value
        if ($this->enablePerEntrySettings && $element && $element->id) {
            $this->settings = $this->getEntrySettings($element->id);
        } else {
            $this->settings = null;
        }

        return $normalizedValue; // Return only the relational value, not the custom settings
    }

    /**
     * Serialize the value, ensuring the settings are included when the entry is saved.
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        // Only serialize the parent relational value
        return parent::serializeValue($value, $element);
    }

    /**
     * Save settings to the database when the element is saved.
     */
    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        parent::afterElementSave($element, $isNew);

        if ($this->enablePerEntrySettings) {
            $request = Craft::$app->getRequest();
            // Adjust the key to match your form structure
            $settings = $request->getBodyParam('fields.fields.' . $this->handle . '.settings');

            Craft::debug(
                'Settings from POST: ' . print_r($settings, true),
                __METHOD__
            );

            if (!$settings) {
                Craft::error('No settings found for field handle: ' . $this->handle, __METHOD__);
                return; // Early exit if no settings found
            }

            if (!$element->id || !$this->id) {
                Craft::error('Missing element ID or field ID while saving settings', __METHOD__);
                return; // Early exit if IDs are missing
            }

            $db = Craft::$app->getDb();

            // Prepare data for insertion or update
            $dataToSave = [
                'entryId' => $element->id,
                'fieldId' => $this->id,
                'allowDirectDownload' => $settings['allowDirectDownload'] ?? null,
                'formTitle' => $settings['formTitle'] ?? null,
                'formDescription' => $settings['formDescription'] ?? null,
                'buttonText' => $settings['buttonText'] ?? null,
                'successMessage' => $settings['successMessage'] ?? null,
                'dateUpdated' => date('Y-m-d H:i:s'),
            ];

            // Check if settings already exist for this entry/field pair
            $existingSettings = (new Query())
                ->from('{{%rapiddownload_settings}}')
                ->where(['entryId' => $element->id, 'fieldId' => $this->id])
                ->one();

            if ($existingSettings) {
                // Update existing record
                $db->createCommand()
                    ->update('{{%rapiddownload_settings}}', $dataToSave, ['id' => $existingSettings['id']])
                    ->execute();

                Craft::debug('Updated settings in rapiddownload_settings table', __METHOD__);
            } else {
                // Insert new record
                $dataToSave['dateCreated'] = date('Y-m-d H:i:s'); // Add dateCreated field for inserts
                $db->createCommand()
                    ->insert('{{%rapiddownload_settings}}', $dataToSave)
                    ->execute();

                Craft::debug('Inserted new settings into rapiddownload_settings table', __METHOD__);
            }
        }
    }
}