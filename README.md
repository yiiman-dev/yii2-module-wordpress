this is a customized wordpress module for yii2

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
## Install Wordpress
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


