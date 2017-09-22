<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpTerms]].
 *
 * @see WpTerms
 */
class WpTermsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpTerms[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpTerms|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
