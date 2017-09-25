this module is a customized WordPress module for yii2

## Installed WordPress
![untitled](https://user-images.githubusercontent.com/11722893/30788124-a851add4-a1a3-11e7-90df-c9b94d64ab55.png)

## install
````
$ composer require amintado/yii2-module-wordpress "*"
````
OR
add this line to composer.json
````
"amintado/yii2-module-wordpress":"*"
````
## Config
add 
````
'cms'=>[
            'class'=> amintado\wordpress\Module::className(),
        ]
````
to frontend/config/main.php file, under modules array,
for example:
````
'modules' => [
        'cms'=>[
                    'class'=> amintado\wordpress\Module::className(),
                ]
]
````
create a php file with this name in root project directory:
`status.php`
add this lines to `status.php`:
````
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
defined('WP_USE_THEMES') or define('WP_USE_THEMES', true);
````
now delete this lines from your Yii2 app index file to prevent conflict:
````
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');
````
## Now Install Wordpress
open this link:
http://yourhost.com/frontend/index.php/cms

then module will create a folder with 'cms' name in your root project directory, and will install WordPress there.

wordpress tables will create automatic in your DB if you are using mysql by default.

after install system will redirect webpage to installed wordpress directory.

## Parameters
after wp installed,you can login to wp with this config:


admin username:wpadmin


admin password: 123456789

## Change Parameters

you can also change default parameters before install WordPress on Your project, just config this parameters in config array in config/main.php file:
````
'modules' => [
        'cms'=>[
                    'class'=> amintado\wordpress\Module::className(),
                    'WeblogTitle'=>'amintado Yii2 WordPress Module',
                    'WeblogUsername'=>'wpadmin',
                    'WeblogEmail'=>'amintado@gmail.com',
                    'WeblogPassword'=>'123456789',
                    'WeblogPublic'=>1
                ]
]
````
## Notic
wordpress admin options and menu will disable in debug mode,
for example if YII_DEBUG=true then all wordpress admin menus and admin bars will disable.

this settings can set or unset in `function.php` file in theme folder.
if you have solution for that, tell me your solution.

## License
This Module is Under GPL3 LICENSE

## Improve
if you want work with WP on YII2 Framework realy, this module is first created,then help to Improve it **:)**

## For Persian Programmers
this WordPress is contain translated and RTL wp layers slider plugin. 
that you can use it in your Project

## use WP functions in Yii2 
if you want use WP functions in yii for example load a post or load an slide in your Yii2 project You must _require_ **wp-load.php** file to your project.

## Attention
Never Use wp-load.php in index.php file.
use it in nedded controllers or actions only.

if you require wp-load.php file in your index.php file or any certain part of project, your CSRF validation in login,logout and every form in your site will conflict with wordpress,and your posted data will filter and will change to null.

## use YII2 functions in  installed WordPress
**wp-load.php** file is contain Yii2 starter php files.

you can see added codes here:
````
require (__DIR__.'/../status.php');

if (strpos(parse_url($_SERVER  ["REQUEST_URI"])['path'],'cms',0)){

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../backend/config/bootstrap.php');
//require(__DIR__ . '/../cms/wp-load.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/../backend/config/main.php'),
    require(__DIR__ . '/../backend/config/main-local.php')
);

(new yii\web\Application($config));

}
````

## WordPress Version
the based wordpress that used in this module:

version:4.8.1
with persian full translated package
