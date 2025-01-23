<?php
namespace highlanddev\rapiddownload\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\fields\Assets;

class RapidDownloadField extends Assets
{
    public static function displayName(): string
    {
        return 'Rapid Download Files';
    }

    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return parent::getInputHtml($value, $element);
    }
}