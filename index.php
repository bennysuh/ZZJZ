<?php
//定义项目名称和路径 若APP_NAME = 'ZZJZ',且ZZJZ是www/test下的目录。则调用的public等目录是在test目录下。
//若APP_NAME='./ZZJZ',则调用的public等目录是在test/ZZJZ目录下。
define('APP_NAME', './zzjz');
define('APP_PATH', './');
// 加载框架入口文件
require( "./ThinkPHP/ThinkPHP.php");
?>