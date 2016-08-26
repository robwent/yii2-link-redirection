<?php

use yii\db\Migration;

/**
 * Handles the creation of table `settings`.
 */
class m160826_021144_create_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('settings', [
          'id' => $this->primaryKey(),
          'setting_name' => $this->string()->notNull(),
          'setting_value' => $this->text(),
          'setting_type' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('settings');
    }
}
