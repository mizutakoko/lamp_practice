<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
$user = get_login_user($db);
if(is_admin($user) === false){
  $buy = select_buy($db, $user['user_id']);
  //dd($buy);
}else{
  $buy = select_admin($db);
}


$token = get_csrf_token(); //token生成
include_once VIEW_PATH.'buy_view.php';
?>