<?php
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'db.php';

// DB利用

function get_item($db, $item_id){ //itemsテーブルの参照
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
      item_id = :item_id
  ";  //条件 item_idが押された時
$array = array(':item_id'=> $item_id);
  return fetch_query($db, $sql, $array);  //一行を実行する
}

function get_items($db, $is_open = false){ //itemsテーブルの参照
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
  if($is_open === true){
    $sql .= '
      WHERE status = 1
    '; //条件 ステータスが表示の物だけ
  }

  return fetch_all_query($db, $sql); //複数行を実行
}

function get_all_items($db){ //全ての商品を参照する関数
  return get_items($db);//DBからitemsテーブルの参照
}

function get_open_items($db){ //参照の条件があったら表示する関数
  return get_items($db, true);//ステータスが表示のものだけ参照
}

function regist_item($db, $name, $price, $stock, $status, $image){
  $filename = get_upload_filename($image);//画像のアップロードファイルの関数
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  $db->beginTransaction(); //トランザクション開始
  if(insert_item($db, $name, $price, $stock, $filename, $status) //商品書き込み 関数
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
  
}

function insert_item($db, $name, $price, $stock, $filename, $status){ //商品書き込み 関数
  $status_value = PERMITTED_ITEM_STATUSES[$status]; //'open' => 1,'close' => 0
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(:name, :price, :stock, :filename, :status_value);
  ";
//PDO execute 処理
$array = array(':name'=>$name, ':price'=>$price, ':stock'=>$stock, ':filename'=>$filename, ':status_value'=>$status_value);
  return execute_query($db, $sql, $array);
}

function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = :status
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  $array = array(':status' => $status, ':item_id' => $item_id);
  return execute_query($db, $sql, $array);
}

function update_item_stock($db, $item_id, $stock){  //アイテム在庫の変更関数
  $sql = "
    UPDATE
      items
    SET
      stock = :stock
    WHERE
      item_id = :item_id
    LIMIT 1
  ";  //itemsテーブルの在庫を変更　条件は$item_id
  $array = array(':stock' => $stock,':item_id' => $item_id);
  return execute_query($db, $sql, $array);  //戻り値　実行準備して実行する関数
}

function destroy_item($db, $item_id){
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  $db->beginTransaction(); //トランザクション開始
  if(delete_item($db, $item['item_id']) //商品の削除と
    && delete_image($item['image'])){ //ファイルの画像が既に存在していたら削除
    $db->commit(); //コミット
    return true;
  }
  $db->rollback(); //ロールバック
  return false;
}

function delete_item($db, $item_id){ //商品の削除 item_idを使って
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  $array = array(':item_id'=>$item_id); //PDO
  return execute_query($db, $sql, $array);
}


// 非DB

function is_open($item){ //$itemのステータスが１のときだけ表示する 関数
  return $item['status'] === 1; //戻り値 ステータス1
}

function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name); //$nameの文字数制限　関数
  $is_valid_item_price = is_valid_item_price($price); //$price正規表現 関数
  $is_valid_item_stock = is_valid_item_stock($stock); //$stockの正規表現
  $is_valid_item_filename = is_valid_item_filename($filename); //ファイルの存在を確認する関数
  $is_valid_item_status = is_valid_item_status($status); //ステータスの存在を確認する関数

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status; //それぞれのtrueかfales(エラ―)を返す
}

function is_valid_item_name($name){ //$nameの文字数制限　関数
  $is_valid = true;
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){ //$nameの文字数１～１００じゃなかったら
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。'); //エラー
    $is_valid = false; //falseを返す
  }
  return $is_valid; //trueかfalse
}

function is_valid_item_price($price){ //$price正規表現 関数
  $is_valid = true;
  if(is_positive_integer($price) === false){ //$priceが整数じゃなかったら
    set_error('価格は0以上の整数で入力してください。'); //エラー
    $is_valid = false;
  }
  return $is_valid; //trueかfalse
}

function is_valid_item_stock($stock){ //$stockの正規表現
  $is_valid = true;
  if(is_positive_integer($stock) === false){ //$stockが整数じゃなかったら
    set_error('在庫数は0以上の整数で入力してください。'); //エラー
    $is_valid = false;
  }
  return $is_valid; //trueかfalse
}

function is_valid_item_filename($filename){ //ファイルの存在を確認する関数
  $is_valid = true;
  if($filename === ''){ //$filenameが空だったら
    $is_valid = false;
  }
  return $is_valid;//trueかfalse
}

function is_valid_item_status($status){ //ステータスの存在を確認する関数
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){  //'open' => 1,'close' => 0, ステータスがなかったら
    $is_valid = false;
  }
  return $is_valid; //trueかfalse
}