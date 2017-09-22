<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpOptions]].
 *
 * @see WpOptions
 */
class WpOptionsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpOptions[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpOptions|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
