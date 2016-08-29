<?php

use yii\db\Migration;

class m160829_211610_seed_settings extends Migration
{
    public function up()
    {
      $this->batchInsert('settings', [
        'setting_name', 'setting_value', 'setting_type'
      ],
      [
        [
          'robots',
          'noindex,follow',
          'text',
        ],
        [
          'statuscode',
          '303',
          'text',
        ],
        [
          'log404',
          1,
          'checkbox',
        ],
        [
          'analytics',
          null,
          'text',
        ],
        [
          'mailto',
          null,
          'text',
        ]
      ]);
    }

    public function down()
    {
        echo "m160829_211610_seed_settings cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
