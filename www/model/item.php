<?php
//関数ファイルの読み込み
require_once MODEL_PATH . 'functions.php';
//設定ファイル読み込み
require_once MODEL_PATH . 'db.php';

// DB利用

function get_item($db, $item_id){ //item_idを条件にitemsテーブルの表示
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = {$item_id}
  ";

  return fetch_query($db, $sql); //一行を実行する
}

function get_items($db, $is_open = false){ //itemsテーブルの表示 $is_openがなかったら
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){ //$is_openがあったら
    $sql .= '
      WHERE status = 1
    '; //条件 ステータス１(公開)を表示
  }

  return fetch_all_query($db, $sql);
}//戻り値 複数行を実行する関数

function get_all_items($db){//itemsテーブルの全てを表示 
  return get_items($db); 
}

function get_open_items($db){ //itemsテーブルの 条件 ステータス１(公開)を表示
  return get_items($db, true);
}

function regist_item($db, $name, $price, $stock, $status, $image){
  $filename = get_upload_filename($image); //アップロードファイルの関数を代入
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  $db->beginTransaction(); //トランザクション開始
  if(insert_item($db, $name, $price, $stock, $filename, $status) //itemsテーブルの書き込み
    && save_image($image, $filename)){ //画像をディレクトリに保存する関数
    $db->commit(); //コミット
    return true;
  }
  $db->rollback(); //ロールバック
  return false;
  
}

function insert_item($db, $name, $price, $stock, $filename, $status){ //itemsテーブルの書き込み
  $status_value = PERMITTED_ITEM_STATUSES[$status]; //open =>1, close =>0
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES('{$name}', {$price}, {$stock}, '{$filename}', {$status_value});
  ";

  return execute_query($db, $sql); //戻り値 実行準備して実行する関数
}

function update_item_status($db, $item_id, $status){ //itemsのステータス更新する関数
  $sql = "
    UPDATE
      items
    SET
      status = {$status}
    WHERE
      item_id = {$item_id}
    LIMIT 1
  "; //条件 item_idと行数 1行
  
  return execute_query($db, $sql); //戻り値 実行準備して実行する関数
}

function update_item_stock($db, $item_id, $stock){  //アイテム在庫の変更関数
  $sql = "
    UPDATE
      items
    SET
      stock = {$stock}
    WHERE
      item_id = {$item_id}
    LIMIT 1
  ";  //itemsテーブルの在庫を変更　条件は$item_id
  
  return execute_query($db, $sql);  //戻り値　実行準備して実行する関数
}

function destroy_item($db, $item_id){ //itemの削除をする関数
  $item = get_item($db, $item_id); //item_idを条件にitemsテーブルの表示する関数を$itemに代入
  if($item === false){ //$itemになにもなかったら
    return false; //戻り値 false
  }
  $db->beginTransaction(); //トランザクション開始
  if(delete_item($db, $item['item_id']) //items item_idと行数一行を条件に削除
    && delete_image($item['image'])){ //item_idをキーにディレクトリの画像を削除
    $db->commit(); //コミット
    return true;
  }
  $db->rollback(); //ロールバック
  return false;
}

function delete_item($db, $item_id){ //items item_idと行数一行を条件に削除
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = {$item_id}
    LIMIT 1
  ";
  
  return execute_query($db, $sql);
} //戻り値 実行準備と実行


// 非DB

function is_open($item){
  return $item['status'] === 1;
}

function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

function is_valid_item_name($name){
  $is_valid = true;
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}