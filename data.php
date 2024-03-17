<?php

namespace Ms\General\Sale\Order;

use Bitrix\Main\Application;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Sale\Order;
use Ms\General\Orm\OrderDataTable;
use Ms\General\Site\Log\Dev;

class Data {

    private int $orderId;

    private bool $noSave = false;

    private bool $needSave = false;

    private ?array $data = null;

    private static array $instance = [];

    private function __construct() {}

    static function getInstance(

        Order|string|int $order,

    ) {

        if ($order instanceof Order) {

            $orderId = $order->getId();
        }
        else {

            $orderId = (int) $order;
        }

        if (static::$instance[$orderId] === null) {
            static::$instance[$orderId] = new static;
            static::$instance[$orderId]->orderId = $orderId;
        }

        return static::$instance[$orderId];
    }

    function save(): self {

        $this->needSave = true;

        if ($this->noSave) {
            return $this;
        }

        $json = json_encode(

            $this->getBase() ?: '',

            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        if (!$json) {

            $valBeforeSave = OrderDataTable::query()
                ->setSelect(['data'])
                ->where('orderId', '=', $this->orderId)
                ->fetch()['data'];

            if ($valBeforeSave) {

                (new Dev('clearNoClearOrderData'))->add(
                    [
                        __FILE__,
                        __LINE__,
                        'orderId' => $this->orderId,
                        '$valBeforeSave' => $valBeforeSave,
                        'CurrentUser::get()->getId()' => CurrentUser::get()->getId(),
                        '$_SERVER' => $_SERVER,
                        'debug_backtrace' => \Ms\General\Site\Log\Dev::getDebugBacktracePrint(),
                    ],
                    'evgeny.babyuk@mi-shop.com',
                );
            }
        }

        $connection = Application::getConnection();

        $sqlInsert = "insert into " . OrderDataTable::getTableName() . " (
            orderId,
            data
        )
        values (
            " . $this->orderId . ",
            '" . $connection->getSqlHelper()->forSql($json) . "'
        )
        on duplicate key update data = values(data)";

        $connection->query($sqlInsert);

        $this->needSave = false;

        return $this;
    }

    function getNeedSave(): bool {

        return $this->needSave;
    }

    function isBaseLoaded(): bool {

        return $this->data !== null;
    }

    function getBase(): array {

        if (!$this->isBaseLoaded()) {

            $this->setBase((array) json_decode(

                OrderDataTable::query()
                    ->setSelect(['data'])
                    ->where('orderId', '=', $this->orderId)
                    ->fetch()['data'],

                true
            ));
        }

        return $this->data;
    }

    function setBase(array $val): self {

        $this->data = $val;

        return $this;
    }

    function set(

        mixed $id,
        mixed $val,

    ): self {

        $this->getBase();

        $this->data[$id] = $val;

        return $this;
    }

    function get(

        mixed $id,

    ): mixed {

        return $this->getBase()[$id];
    }

    function setNoSave(

        bool $val,

    ): self {

        $this->noSave = $val;

        return $this;
    }

    function getNoSave(): bool {

        return $this->noSave;
    }

    function remove(

        mixed $id,

    ): self {

        $this->getBase();

        unset($this->data[$id]);

        return $this;
    }
}