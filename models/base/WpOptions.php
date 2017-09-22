<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_options".
 *
 * @property string $option_id
 * @property string $option_name
 * @property string $option_value
 * @property string $autoload
 */
class WpOptions extends \yii\db\ActiveRecord
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
            [['option_value'], 'required'],
            [['option_value'], 'string'],
            [['option_name'], 'string', 'max' => 191],
            [['autoload'], 'string', 'max' => 20],
            [['option_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_options';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'option_id' => Yii::t('atwp', 'Option ID'),
            'option_name' => Yii::t('atwp', 'Option Name'),
            'option_value' => Yii::t('atwp', 'Option Value'),
            'autoload' => Yii::t('atwp', 'Autoload'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpOptionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpOptionsQuery(get_called_class());
    }
}
