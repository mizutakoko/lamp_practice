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
$token = get_post('token');
if(is_valid_csrf_token($token)===false){ //$tokenがないとき
  unset($_SESSION['csrf_token']); //sessionから送られてきたtokenを削除する
  redirect_to(LOGIN_URL); //ログイン画面にリダイレクトする
}
unset($_SESSION['csrf_token']);

$db = get_db_connect();
$user = get_login_user($db);
//dd($user);
$buy_id = get_post('buy_id'); //post通信でのbuy_idの取得
//dd($buy_id);
  $buy = select_buy_id($db, $buy_id); //buy_id 購入時間 合計金額
//dd($buy);
  $select_details = select_details($db, $buy_id); //購入明細 商品名 価格 購入数 小計



include_once VIEW_PATH.'details_view.php';
?>