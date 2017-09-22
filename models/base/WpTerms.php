<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_terms".
 *
 * @property string $term_id
 * @property string $name
 * @property string $slug
 * @property string $term_group
 */
class WpTerms extends \yii\db\ActiveRecord
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
            [['term_group'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_terms';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'term_id' => Yii::t('atwp', 'Term ID'),
            'name' => Yii::t('atwp', 'Name'),
            'slug' => Yii::t('atwp', 'Slug'),
            'term_group' => Yii::t('atwp', 'Term Group'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpTermsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpTermsQuery(get_called_class());
    }
}
