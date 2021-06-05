<?php 
//設定ファイルの読み込み
require_once MODEL_PATH . 'functions.php';
//関数ファイルの読み込み
require_once MODEL_PATH . 'db.php';

function get_user_carts($db, $user_id){   //ユーザーカートの関数 itemsとcartsの表示　結合して条件user_id
  $sql = "    
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = {$user_id}
  ";
  return fetch_all_query($db, $sql);  //戻り値は複数行を実行する関数
}

function get_user_cart($db, $user_id, $item_id){  //ユーザーカートの関数　itemsとcartsの表示　条件user_idとitem_id
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = {$user_id}
    AND
      items.item_id = {$item_id}
  ";

  return fetch_query($db, $sql);  //戻り値　一行を実行する

}

function add_cart($db, $user_id, $item_id ) { //カート関数
  $cart = get_user_cart($db, $user_id, $item_id); //$cartにユーザーカートの関数を代入
  if($cart === false){  //もし$cartがなかったら
    return insert_cart($db, $user_id, $item_id);
  } //戻り値　カートに書き込みをする関数
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}   //$cartがあるときは数量更新する関数

function insert_cart($db, $user_id, $item_id, $amount = 1){ //cartsテーブルに書き込み
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES({$item_id}, {$user_id}, {$amount})
  ";  //insertする値 $item_id $user_id $amount

  return execute_query($db, $sql);  //戻り値　実行準備して実行する関数
}

function update_cart_amount($db, $cart_id, $amount){  //cartの数量変更をする関数
  $sql = "
    UPDATE
      carts
    SET
      amount = {$amount}
    WHERE
      cart_id = {$cart_id}
    LIMIT 1
  ";  //更新対象$amount   条件$cart_id
  return execute_query($db, $sql);  //戻り値　実行準備して実行する
}

function delete_cart($db, $cart_id){  //cartの削除
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = {$cart_id}
    LIMIT 1
  ";  //cartsテーブルの条件$cart_idで削除

  return execute_query($db, $sql);  //実行準備と実行関数
}

function purchase_carts($db, $carts){ //cartsの更新があってエラーがなかったら削除する関数
  if(validate_cart_purchase($carts) === false){ //もし$cartsのエラー関数がなかったら　
    return false;
  }
  foreach($carts as $cart){ //for文で$cartsを回す
    if(update_item_stock(   //アイテム在庫の変更関数
        $db, 
        $cart['item_id'],   //item_idをキーに
        $cart['stock'] - $cart['amount']  //在庫数－数量
      ) === false){ //更新がなかったら
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  
  delete_user_carts($db, $carts[0]['user_id']); //ユーザーのカート削除関数
} //user_idをキーに削除する

function delete_user_carts($db, $user_id){  //ユーザーのカート削除関数
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = {$user_id}
  ";  //cartsテーブルの削除　条件$user_id

  execute_query($db, $sql); //実行準備と実行
}


function sum_carts($carts){  //cartsの計算関数
  $total_price = 0; //0を代入
  foreach($carts as $cart){  //for文で$cartsを回す
    $total_price += $cart['price'] * $cart['amount'];
  } //合計金額 ＝ 合計金額　＋（金額 × 数量）
  return $total_price;  //戻り値　合計金額
}

function validate_cart_purchase($carts){  //$cartsのエラー関数
  if(count($carts) === 0){  //もし$cartsがカウント0だったら
    set_error('カートに商品が入っていません。');
    return false;
  }
  foreach($carts as $cart){   //$cartsをfor文で回して
    if(is_open($cart) === false){ //ファイルopして$cartがなかったら
      set_error($cart['name'] . 'は現在購入できません。');
    }
    if($cart['stock'] - $cart['amount'] < 0){ //在庫数－数量が0以下の時
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  if(has_error() === true){ //レスポンス(リクエストしてレスポンスしたとき)でエラーがある時
    return false;
  }
  return true;
}

