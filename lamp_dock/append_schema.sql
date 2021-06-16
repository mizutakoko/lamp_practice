-- 購入テーブル
-- user_id 注文番号 注文日時 該当の注文の合計金額
create table buy ( 
    buy_id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    buy_time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    buy_total int(11) NOT NULL,
    PRIMARY KEY(buy_id)
)


--　購入明細履歴テーブル
-- 明細番号 注文番号 item_id 購入時の商品価格 購入数 小計 
CREATE TABLE details(
    details_id int(11) NOT NULL AUTO_INCREMENT,
    buy_id int(11) NOT NULL,
    item_id int(11) NOT NULL,
    price int(11) NOT NULL,
    amount int(11) NOT NULL,
    PRIMARY KEY(details_id)
)