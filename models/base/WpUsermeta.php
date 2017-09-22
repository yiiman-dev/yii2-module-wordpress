<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_usermeta".
 *
 * @property string $umeta_id
 * @property string $user_id
 * @property string $meta_key
 * @property string $meta_value
 */
class WpUsermeta extends \yii\db\ActiveRecord
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
            [['user_id'], 'integer'],
            [['meta_value'], 'string'],
            [['meta_key'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_usermeta';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'umeta_id' => Yii::t('atwp', 'Umeta ID'),
            'user_id' => Yii::t('atwp', 'User ID'),
            'meta_key' => Yii::t('atwp', 'Meta Key'),
            'meta_value' => Yii::t('atwp', 'Meta Value'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpUsermetaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpUsermetaQuery(get_called_class());
    }
}
