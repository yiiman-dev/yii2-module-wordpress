<?php

namespace amintado\wordpress\models;

/**
 * This is the ActiveQuery class for [[WpPosts]].
 *
 * @see WpPosts
 */
class WpPostsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return WpPosts[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WpPosts|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
