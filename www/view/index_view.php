<?php header("X-FRAME-OPTIONS: DENY"); //token盗難防止 ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  
  <title>商品一覧</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'index.css')); ?>">
</head>
<body>
    <?php include VIEW_PATH . 'templates/header_logined.php'; ?>

    
      <div style="margin-left: 1320px">
        <form method="get"  action="index.php">
          <div style="display:flex">
            <select name="Lineup">
              <option value="New" <?php if($Line_up ==='New'||$Line_up === ''){print h('selected');}?>>新着順</option>
              <option value="pricecheap" <?php if($Line_up ==='pricecheap'){print h('selected');}?> >価格の安い順</option>
              <option value="pricehigh" <?php if($Line_up ==='pricehigh'){print h('selected');}?> >価格の高い順</option>
            </select>
            <div>
              <input type="submit" value="並べ替え">
            </div>
          </div>  
        </form>
      </div> 
  <div class="container">
    <h1>商品一覧</h1>
    <?php include VIEW_PATH . 'templates/messages.php'; ?>

    <div class="card-deck">
      <div class="row">
      <?php foreach($Line_up_New as $item){ ?>
        <div class="col-6 item">
          <div class="card h-100 text-center">
            <div class="card-header">
              <?php print(h($item['name'])); ?>
            </div>
            <figure class="card-body">
              <img class="card-img" src="<?php print(h(IMAGE_PATH . $item['image'])); ?>">
              <figcaption>
                <?php print(h(number_format($item['price']))); ?>円
                <?php if($item['stock'] > 0){ ?>
                  <form action="index_add_cart.php" method="post">
                    <input type="hidden" name="token" value="<?php print h($token); ?>">
                    <input type="submit" value="カートに追加" class="btn btn-primary btn-block">
                    <input type="hidden" name="item_id" value="<?php print(h($item['item_id'])); ?>">
                  </form>
                <?php } else { ?>
                  <p class="text-danger">現在売り切れです。</p>
                <?php } ?>
              </figcaption>
            </figure>
          </div>
        </div>
      <?php } ?>
      </div>
    </div>
  </div>
  
</body>
</html>