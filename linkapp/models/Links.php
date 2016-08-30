<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "links".
 *
 * @property integer $id
 * @property string $short_url
 * @property string $full_url
 * @property integer $status
 * @property string $description
 * @property string $published
 */
class Links extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'links';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['short_url', 'full_url'], 'required'],
            [['full_url', 'description'], 'string'],
            [['status'], 'integer'],
            [['published'], 'safe'],
            [['short_url'], 'string', 'max' => 45],
            [['short_url'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'short_url' => 'Short Url',
            'full_url' => 'Full Url',
            'status' => 'Status',
            'description' => 'Description',
            'published' => 'Published',
        ];
    }

    /**
     * @inheritdoc
     * @return LinksQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LinksQuery(get_called_class());
    }

    public function behaviors()
    {
      return [
        'timestamp' => [
          'class' => 'yii\behaviors\TimestampBehavior',
          'attributes' => [
            ActiveRecord::EVENT_BEFORE_INSERT => ['published'],
          ],
          'value' => function() { return date('Y-m-d H:i:s'); },
        ],
      ];
    }

}
