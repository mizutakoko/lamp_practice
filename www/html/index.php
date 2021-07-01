<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
$user = get_login_user($db);

$Line_up = get_get('Lineup');

//if文でそれぞれ分けて$Line_up(New・pricecheap・pricehigh) select文
if(($Line_up === 'New')||($Line_up ==="")){
  $Line_up_New = get_Lineup_New($db); //新着順

}else if($Line_up === 'pricecheap'){
  $Line_up_New = get_Lineup_asc($db); //安い順

}else if($Line_up === 'pricehigh'){
  $Line_up_New = get_Lineup_desc($db); //高い順

}

$token = get_csrf_token(); //tokenの生成

include_once VIEW_PATH . 'index_view.php';