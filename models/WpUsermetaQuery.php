<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpUsermeta]].
 *
 * @see WpUsermeta
 */
class WpUsermetaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpUsermeta[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpUsermeta|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
