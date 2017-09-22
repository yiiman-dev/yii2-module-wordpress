<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpTermRelationships]].
 *
 * @see WpTermRelationships
 */
class WpTermRelationshipsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpTermRelationships[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpTermRelationships|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
