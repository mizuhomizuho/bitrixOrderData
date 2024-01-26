# bitrixOrderData

Простой класс реализует запись json данных по заказам
в отдельную табличку.

```sql
-- mi_sale_order_data определение

CREATE TABLE `mi_sale_order_data` (
  `orderId` int(11) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  PRIMARY KEY (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
```

Примеры:

```php
$meow = \Ms\General\Sale\Order\Data::getInstance(88888888)
    ->get('meow');

\Ms\General\Sale\Order\Data::getInstance(88888888)
    ->set('meow', 8)
    ->save();
```

```php
$dataObj = \Ms\General\Sale\Order\Data::getInstance($order);

if (!$dataObj->isBaseLoaded()) {
    $dataObj->setBase((array) json_decode(
        $row['ORDER_DATA'],
        true
    ));
}

$dataObjNoSaveBu = $dataObj->getNoSave();
$dataObj->setNoSave(true);

$meow = $dataObj->get('meow');

$dataObj->set('meow', 8)->save();
$dataObj->set('meow-meow', 88);
$dataObj->set('meow-meow-meow', 888)->save();

if ($dataObj->getNeedSave()) {
    $dataObj->setNoSave(false)->save();
}
$dataObj->setNoSave($dataObjNoSaveBu);
```