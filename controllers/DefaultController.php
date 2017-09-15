<?php
/**
 * Created by PhpStorm.
 * User: amin__000
 * Date: 9/15/2017
 * Time: 7:33 PM
 */

namespace amintado\wordpress\controllers;


use yii\web\Controller;

class DefaultController extends Controller
{

    public function actionIndex()
    {
        define('WP_USE_THEMES', true);
        require (__DIR__. '/../wordpress/wp-blog-header.php');
    }
}