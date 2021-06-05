<?php

function get_db_connect(){
  // MySQL用のDSN文字列
  $dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST .';charset='.DB_CHARSET;
 
  try {
    // データベースに接続
    $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    exit('接続できませんでした。理由：'.$e->getMessage() );
  }
  return $dbh;
}

function fetch_query($db, $sql, $params = array()){   //一行を実行する関数
  try{
    $statement = $db->prepare($sql);  //実行を準備する
    $statement->execute($params);   //実行する
    return $statement->fetch();   //一行
  }catch(PDOException $e){    //例外処理
    set_error('データ取得に失敗しました。');
  }
  return false;
}

function fetch_all_query($db, $sql, $params = array()){   //複数行を実行する関数
  try{
    $statement = $db->prepare($sql);  //実行準備
    $statement->execute($params);   //実行　$params(配列)
    return $statement->fetchAll();  //複数行
  }catch(PDOException $e){  //例外処理
    set_error('データ取得に失敗しました。');
  }
  return false;
}

function execute_query($db, $sql, $params = array()){ //実行準備して実行する関数
  try{
    $statement = $db->prepare($sql);  //実行準備
    return $statement->execute($params);  //実行
  }catch(PDOException $e){    //例外処理
    set_error('更新に失敗しました。');
  }
  return false;
}