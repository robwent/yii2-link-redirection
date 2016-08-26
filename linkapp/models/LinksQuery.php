<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Links]].
 *
 * @see Links
 */
class LinksQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Links[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Links|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
