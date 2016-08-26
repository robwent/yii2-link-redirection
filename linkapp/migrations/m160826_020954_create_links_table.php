<?php

use yii\db\Migration;

/**
 * Handles the creation of table `links`.
 */
class m160826_020954_create_links_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('links', [
            'id' => $this->primaryKey(),
            'short_url' => $this->string(45)->notNull()->unique(),
            'full_url' => $this->text()->notNull(),
            'status' => $this->boolean(),
            'description' => $this->text(),
            'published' => $this->datetime(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('links');
    }
}
