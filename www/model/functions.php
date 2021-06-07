<?php

function dd($var){  //デバッグ関数
  var_dump($var); 
  exit(); // 終了
}

function redirect_to($url){ //リダイレクト関数
  header('Location: ' . $url); 
  exit;
}

function get_get($name){  //get通信関数
  if(isset($_GET[$name]) === true){ //get通信で$nameがあるとき
    return $_GET[$name];  //戻り値　$_GET[$name]を返す
  };
  return '';  //戻り値　空
}

function get_post($name){ //post通信関数
  if(isset($_POST[$name]) === true){  //post通信で$nameがあるとき
    return $_POST[$name]; //戻り値　$_POST[$name]を返す
  };
  return '';  //戻り値　空
}

function get_file($name){  //ファイル関数
  if(isset($_FILES[$name]) === true){  //ファイルに$nameがあるとき
    return $_FILES[$name];  //戻り値　$_FILES[$name]を返す
  };
  return array(); //配列の空
}

function get_session($name){  //セッション関数
  if(isset($_SESSION[$name]) === true){ 
    return $_SESSION[$name];  //戻り値　$_SESSION[$name]を返す
  };
  return '';  //戻り値　空
}

function set_session($name, $value){  //$_SESSION[キー]に変数を代入する関数
  $_SESSION[$name] = $value;  
}

function set_error($error){ //変数を$_SESSION['__errors'][]に代入する関数
  $_SESSION['__errors'][] = $error;
}

function get_errors(){
  $errors = get_session('__errors');  //$errorsにセッション関数を代入
  if($errors === ''){ //エラーがなかったら
    return array(); //戻り値　配列を返す
  }
  set_session('__errors',  array());  //配列のエラーがあるとき
  return $errors; //戻り値 $errorsを返す
}

function has_error(){ //エラーがあるか
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}//戻り値 エラーがあるかどうか エラーの数が0じゃない

function set_message($message){  //メッセージを表示する関数
  $_SESSION['__messages'][] = $message;
}

function get_messages(){  //メッセージがある時とない時の関数
  $messages = get_session('__messages');
  if($messages === ''){ //もしメッセージがないときは
    return array(); //戻り値　配列を返す
  }
  set_session('__messages',  array());
  return $messages; //メッセージを配列に入れて$messagesを戻り値にする
}

function is_logined(){  //ログイン関数
  return get_session('user_id') !== ''; //user_idが空じゃなければ$_SESSION['user_id']を返す
}

function get_upload_filename($file){  //アップロードファイルの関数
  if(is_valid_upload_image($file) === false){ //ファイル形式の関数
    return '';
  }
  $mimetype = exif_imagetype($file['tmp_name']);//$image['tmp_name']の画像を調べて変数に代入
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];  //変数の画像のタイプ(jpg,png)を$extに代入
  return get_random_string() . '.' . $ext;
}

function get_random_string($length = 20){ //ランダムな文字を作る(20文字)関数
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
} //文字列の　文字数20文字で　　同じIDを作らない

function save_image($image, $filename){ //画像を保存する関数
  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
} //戻り値 ディレクトリに保存 入力された画像名 ディレクトリに入力されたファイル名

function delete_image($filename){ //画像削除
  if(file_exists(IMAGE_DIR . $filename) === true){ //ディレクトリに同じファイル名がある場合
    unlink(IMAGE_DIR . $filename); //ディレクトリの画像ファイルを削除する
    return true; //削除出来たらtrueを返す
  }
  return false; //出来なかったらfalseを返す
  
}


//整数型の最大値を求める関数
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  $length = mb_strlen($string); //$stringの文字列の長さを取得
  return ($minimum_length <= $length) && ($length <= $maximum_length);
} //戻り値 最小値の長さは$length以下 $lengthは最大値以下

function is_alphanumeric($string){ //半角英数字が含まれているか確認する関数
  return is_valid_format($string, REGEXP_ALPHANUMERIC); //$string, /\A[0-9a-zA-Z]+\z/ 
} //戻り値 正規表現 (文字列の先頭が[0-9a-zA-Z]0回以上繰り返して末尾が9,z,Z)

function is_positive_integer($string){ //半角数字が正の整数か確認する関数
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER); //$string, /\A([1-9][0-9]*|0)\z/ (文字列の先頭が[1-9][0-9]0回以上繰り返すまたは文字列の最後が0)
} //戻り値 正規表現 (文字列の先頭が[1-9][0-9]0回以上繰り返すまたは文字列の末尾が0)

function is_valid_format($string, $format){ //$string,$formatの正規表現
  return preg_match($format, $string) === 1;
} //戻り値 正規表現 半角英字


function is_valid_upload_image($image){  //ファイル形式の関数
  if(is_uploaded_file($image['tmp_name']) === false){ //アップロードファイルの$imageに入っているファイル形式が指定と違う場合
    set_error('ファイル形式が不正です。');
    return false;
  }
  $mimetype = exif_imagetype($image['tmp_name']); //$image['tmp_name']の画像を調べる
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){  //ファイルのタイプ(jpg,png)が違う場合
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    return false;
  }
  return true;
}

function h($str){
  return htmlspecialchars($str,ENT_QUOTES,'UTF-8');

}