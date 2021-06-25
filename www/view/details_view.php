<?php header("X-FRAME-OPTIONS: DENY"); //token盗難防止 ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入明細</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'admin.css')); ?>">
</head>
<body>
  <?php include VIEW_PATH . 'templates/header_logined.php'; ?>
  <h1>購入明細</h1>

  <div class="container">
    <?php include VIEW_PATH . 'templates/messages.php'; ?>
    <table class="display-flex">
      <p>注文番号：<?php print(h($buy['buy_id'])); ?>
      　購入日時：<?php print(h($buy['buy_time'])); ?>
      　合計金額：<?php print(h($buy['buy_total'])); ?></p>
    </table>
    <?php //dd($select_details); ?>
    <?php if(count($select_details) > 0){ ?>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>商品名</th>
            <th>価格</th>
            <th>購入数</th>
            <th>小計</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($select_details as $details){ ?>
          <tr>
            <td><?php print(h($details['name'])); ?></td>
            <td><?php print(h(number_format($details['price']))); ?>円</td>
            <td>
                <?php print(h($details['amount'])); ?>個
            </td>
            <td><?php print(h(number_format($details['price'] * $details['amount']))); ?>円</td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>購入明細がありません</p>
    <?php } ?> 
  </div>
</body>
</html>