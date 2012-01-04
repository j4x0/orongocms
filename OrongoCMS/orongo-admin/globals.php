<?php
/**
 * @author Jaco Ruit
 */

//FIXME same as ../globals.php

session_start();

require '../lib/function_load.php';
try{ load('../lib'); }catch(Exception $e){ die($e->getMessage()); }

$db = new Database('../config.php');
$smarty = new Smarty();
$smarty->compile_dir = "../smarty/compile"; 
$smarty->cache_dir = "../smarty/cache"; 
$smarty->config_dir = "../smarty/config"; 
$style = Settings::getStyle('../');

define('RANK_ADMIN', 3);
define('RANK_WRITER', 2);
define('RANK_USER', 1);
define('VERSION_NUMBER', 0.1);

define('ARTICLE_NOT_EXIST', 200);
define('PAGE_NOT_EXIST', 300);
define('USER_NOT_EXIST', 400);

define('PAGE_PAGE', 600);
define('PAGE_INDEX', 700);
define('PAGE_ARTICLE', 800);


?>
