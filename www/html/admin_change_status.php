<?php
//設定ファイル接続
require_once '../conf/const.php';
//関数ファイル接続
require_once MODEL_PATH . 'functions.php';
//userファイル接続
require_once MODEL_PATH . 'user.php';
//itemファイル接続
require_once MODEL_PATH . 'item.php';

session_start();  //セッション

if(is_logined() === false){ //もしログインしなかったらログイン画面に移動する
  redirect_to(LOGIN_URL);
}

$db = get_db_connect(); //DB接続関数

$user = get_login_user($db);  //ログインしたuser 引数に$dbh

if(is_admin($user) === false){  //リダイレクト関数　$userじゃなかったらログイン画面に移動
  redirect_to(LOGIN_URL);
}

$item_id = get_post('item_id'); 
$changes_to = get_post('changes_to');

if($changes_to === 'open'){
  update_item_status($db, $item_id, ITEM_STATUS_OPEN);
  set_message('ステータスを変更しました。');
}else if($changes_to === 'close'){
  update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
  set_message('ステータスを変更しました。');
}else {
  set_error('不正なリクエストです。');
}


redirect_to(ADMIN_URL);