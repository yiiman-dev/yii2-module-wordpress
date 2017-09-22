<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_users".
 *
 * @property string $ID
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property integer $user_status
 * @property string $display_name
 */
class WpUsers extends \yii\db\ActiveRecord
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
            [['user_registered'], 'safe'],
            [['user_status'], 'integer'],
            [['user_login'], 'string', 'max' => 60],
            [['user_pass', 'user_activation_key'], 'string', 'max' => 255],
            [['user_nicename'], 'string', 'max' => 50],
            [['user_email', 'user_url'], 'string', 'max' => 100],
            [['display_name'], 'string', 'max' => 250]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_users';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => Yii::t('atwp', 'ID'),
            'user_login' => Yii::t('atwp', 'User Login'),
            'user_pass' => Yii::t('atwp', 'User Pass'),
            'user_nicename' => Yii::t('atwp', 'User Nicename'),
            'user_email' => Yii::t('atwp', 'User Email'),
            'user_url' => Yii::t('atwp', 'User Url'),
            'user_registered' => Yii::t('atwp', 'User Registered'),
            'user_activation_key' => Yii::t('atwp', 'User Activation Key'),
            'user_status' => Yii::t('atwp', 'User Status'),
            'display_name' => Yii::t('atwp', 'Display Name'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpUsersQuery(get_called_class());
    }
}
