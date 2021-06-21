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
if(is_valid_csrf_token($token) === false){ //tokenがなかった場合
  unset($_SESSION['csrf_token']); //sessionで送信されてきたtokenを削除する
  redirect_to(LOGIN_URL); //ログイン画面にリダイレクトする
}
unset($_SESSION['csrf_token']); //sessionで送信されてきたtokenを削除する

$db = get_db_connect();
$user = get_login_user($db);

$carts = get_user_carts($db, $user['user_id']);

$db -> beginTransaction(); //トランザクション開始

if(purchase_carts($db, $carts) === false){
  set_error('商品が購入できませんでした。');
  $db -> rollback(); //ロールバック
  redirect_to(CART_URL);
} 

$total_price = sum_carts($carts);

$insert_buy = insert_buy($db,$user['user_id'],$total_price);//user_id $total_price(合計金額) 購入履歴テーブルの追加
if($insert_buy===false){
  $db -> rollback(); //ロールバック
  set_err('商品購入履歴の追加ができませんでした');
  redirect_to(CART_URL);
}
$buy_id = $db ->lastInsertId(); //最後に挿入された行のIDを取得する
$insert_details = details_insert($db, $buy_id, $carts);//$cartsを使ってitem_idと商品価格と個数を購入明細テーブルに追加
if($insert_details===false){
  $db -> rollback(); //ロールバック
  set_err('商品購入履歴明細の追加ができませんでした');
  redirect_to(CART_URL);
}


  $db -> commit(); //両方ともtrueの時はコミット


include_once '../view/finish_view.php';