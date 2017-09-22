<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_term_relationships".
 *
 * @property string $object_id
 * @property string $term_taxonomy_id
 * @property integer $term_order
 */
class WpTermRelationships extends \yii\db\ActiveRecord
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
            [['object_id', 'term_taxonomy_id'], 'required'],
            [['object_id', 'term_taxonomy_id', 'term_order'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_term_relationships';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'object_id' => Yii::t('atwp', 'Object ID'),
            'term_taxonomy_id' => Yii::t('atwp', 'Term Taxonomy ID'),
            'term_order' => Yii::t('atwp', 'Term Order'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpTermRelationshipsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpTermRelationshipsQuery(get_called_class());
    }
}
