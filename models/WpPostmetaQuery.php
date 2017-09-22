<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpPostmeta]].
 *
 * @see WpPostmeta
 */
class WpPostmetaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpPostmeta[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpPostmeta|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
