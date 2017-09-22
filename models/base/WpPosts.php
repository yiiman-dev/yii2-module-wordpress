<?php

namespace amintado\wordpress\models\base;

use Yii;

/**
 * This is the base model class for table "wp_posts".
 *
 * @property string $ID
 * @property string $post_author
 * @property string $post_date
 * @property string $post_date_gmt
 * @property string $post_content
 * @property string $post_title
 * @property string $post_excerpt
 * @property string $post_status
 * @property string $comment_status
 * @property string $ping_status
 * @property string $post_password
 * @property string $post_name
 * @property string $to_ping
 * @property string $pinged
 * @property string $post_modified
 * @property string $post_modified_gmt
 * @property string $post_content_filtered
 * @property string $post_parent
 * @property string $guid
 * @property integer $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property string $comment_count
 */
class WpPosts extends \yii\db\ActiveRecord
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
            [['post_author', 'post_parent', 'menu_order', 'comment_count'], 'integer'],
            [['post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt'], 'safe'],
            [['post_content', 'post_title', 'post_excerpt', 'to_ping', 'pinged', 'post_content_filtered'], 'required'],
            [['post_content', 'post_title', 'post_excerpt', 'to_ping', 'pinged', 'post_content_filtered'], 'string'],
            [['post_status', 'comment_status', 'ping_status', 'post_type'], 'string', 'max' => 20],
            [['post_password', 'guid'], 'string', 'max' => 255],
            [['post_name'], 'string', 'max' => 200],
            [['post_mime_type'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wp_posts';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => Yii::t('atwp', 'ID'),
            'post_author' => Yii::t('atwp', 'Post Author'),
            'post_date' => Yii::t('atwp', 'Post Date'),
            'post_date_gmt' => Yii::t('atwp', 'Post Date Gmt'),
            'post_content' => Yii::t('atwp', 'Post Content'),
            'post_title' => Yii::t('atwp', 'Post Title'),
            'post_excerpt' => Yii::t('atwp', 'Post Excerpt'),
            'post_status' => Yii::t('atwp', 'Post Status'),
            'comment_status' => Yii::t('atwp', 'Comment Status'),
            'ping_status' => Yii::t('atwp', 'Ping Status'),
            'post_password' => Yii::t('atwp', 'Post Password'),
            'post_name' => Yii::t('atwp', 'Post Name'),
            'to_ping' => Yii::t('atwp', 'To Ping'),
            'pinged' => Yii::t('atwp', 'Pinged'),
            'post_modified' => Yii::t('atwp', 'Post Modified'),
            'post_modified_gmt' => Yii::t('atwp', 'Post Modified Gmt'),
            'post_content_filtered' => Yii::t('atwp', 'Post Content Filtered'),
            'post_parent' => Yii::t('atwp', 'Post Parent'),
            'guid' => Yii::t('atwp', 'Guid'),
            'menu_order' => Yii::t('atwp', 'Menu Order'),
            'post_type' => Yii::t('atwp', 'Post Type'),
            'post_mime_type' => Yii::t('atwp', 'Post Mime Type'),
            'comment_count' => Yii::t('atwp', 'Comment Count'),
        ];
    }


    /**
     * @inheritdoc
     * @return \amintado\wordpress\models\WpPostsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \amintado\wordpress\models\WpPostsQuery(get_called_class());
    }
}
