<?php
namespace highlanddev\rapiddownload\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        // Create downloads tracking table
        if (!$this->db->tableExists('{{%rapiddownload_downloads}}')) {
            $this->createTable('{{%rapiddownload_downloads}}', [
                'id' => $this->primaryKey(),
                'email' => $this->string()->notNull(),
                'pageUrl' => $this->string(),
                'filenames' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        // Create settings table for storing field-specific settings
        if (!$this->db->tableExists('{{%rapiddownload_settings}}')) {
            $this->createTable('{{%rapiddownload_settings}}', [
                'id' => $this->primaryKey(),
                'entryId' => $this->integer()->notNull(), // Relates to the entry this setting belongs to
                'fieldId' => $this->integer()->notNull(), // Relates to the field this setting belongs to
                'allowDirectDownload' => $this->boolean(),
                'formTitle' => $this->string(),
                'formDescription' => $this->text(),
                'buttonText' => $this->string(),
                'successMessage' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Add indexes and foreign key constraints
            $this->createIndex(
                'idx_rapiddownload_settings_entryId_fieldId',
                '{{%rapiddownload_settings}}',
                ['entryId', 'fieldId']
            );

            $this->addForeignKey(
                'fk_rapiddownload_settings_entry',
                '{{%rapiddownload_settings}}',
                'entryId',
                '{{%entries}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        return true;
    }

    public function safeDown()
    {
        // Drop the settings table
        $this->dropTableIfExists('{{%rapiddownload_settings}}');

        // Drop the downloads tracking table
        $this->dropTableIfExists('{{%rapiddownload_downloads}}');

        return true;
    }
}