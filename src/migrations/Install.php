<?php
namespace highlanddev\rapiddownload\migrations;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
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
        return true;
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%rapiddownload_downloads}}');
        return true;
    }
}