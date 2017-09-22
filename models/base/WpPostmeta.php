<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_postmeta".
 *
 * @property string $meta_id
 * @property string $post_id
 * @property string $meta_key
 * @property string $meta_value
 */
class WpPostmeta extends \yii\db\ActiveRecord
{
    use \mootensai\relation\RelationTrait;


    /**
    * This function helps \mootensai\relation\RelationTrait runs faster
    * @return array relation names of this model
    */
    public function relationNames()
    {
        return [
            ''
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id'], 'integer'],
            [['meta_value'], 'string'],
            [['meta_key'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_postmeta';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'meta_id' => Yii::t('atwp', 'Meta ID'),
            'post_id' => Yii::t('atwp', 'Post ID'),
            'meta_key' => Yii::t('atwp', 'Meta Key'),
            'meta_value' => Yii::t('atwp', 'Meta Value'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpPostmetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpPostmetaQuery(get_called_class());
    }
}
