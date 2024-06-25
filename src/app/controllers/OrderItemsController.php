<?php

class OrderItemsController extends BaseController
{
    public function getOrderItems()
    {
        try {
            $this->allow("GET");

            $filter = "";
            $orderStatus = query("orderStatus");
            if ($orderStatus) {
                $filter .= "od.status = '{$orderStatus}' AND ";
            }

            $ordersItemsData = [];
            $ordersItems = R::getAll("
                SELECT 
                    oi.id AS orderitems_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                    p.name AS product_name,
                    od.quantity AS product_quantity,
                    p.price AS product_price,
                    c.name AS category_name,
                    od.status AS orders_status,
                    CONCAT('public/uploads/', a.filename) AS product_img
                FROM orderitems AS oi
                INNER JOIN users AS u ON oi.user_id = u.id
                INNER JOIN orders AS od ON oi.order_id = od.id
                INNER JOIN products AS p ON oi.product_id = p.id
                INNER JOIN categories AS c ON p.category_id = c.id
                INNER JOIN attachments AS a ON p.id = a.ref_id
                ORDER BY oi.id DESC
            "); 
            foreach ($ordersItems as $ordersItem) { 
                $ordersItemsData[] = [
                    "orderId" => (int)$ordersItem["orderitems_id"],
                    "fullName" => $ordersItem["full_name"],
                    "productName" => $ordersItem["product_name"],
                    "productQuantity" => (int)$ordersItem["product_quantity"],
                    "productPrice" => (float)$ordersItem["product_price"],
                    "productImg" => URL_SCHEME . $ordersItem["product_img"],
                    "categoryName" => $ordersItem["category_name"],
                    "orderStatus" => $ordersItem["orders_status"],
                ];
            }

            R::close();
            $this->toJSON(["message" => "get ordersitems..", "ordersItems" => $ordersItemsData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create order items.." . $e->getMessage()], 500);
        }
    }

    public function getOrderItemsByUser()  
    {
        try {
            $this->allow("GET");

            $userId = query("userId");

            $filter = "";
            $orderStatus = query("orderStatus");
            if ($orderStatus) {
                $filter .= "od.status = '{$orderStatus}' AND ";
            }

            $ordersItemsData = [];
            $ordersItems = R::getAll("
                SELECT 
                    oi.id AS orderitems_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                    p.name AS product_name,
                    od.quantity AS product_quantity,
                    p.price AS product_price,
                    c.name AS category_name,
                    od.status AS orders_status,
                    CONCAT('public/uploads/', a.filename) AS product_img
                FROM orderitems AS oi
                INNER JOIN users AS u ON oi.user_id = u.id
                INNER JOIN orders AS od ON oi.order_id = od.id
                INNER JOIN products AS p ON oi.product_id = p.id
                INNER JOIN categories AS c ON p.category_id = c.id
                INNER JOIN attachments AS a ON p.id = a.ref_id
                WHERE {$filter} u.id = ? 
                ORDER BY oi.id DESC
            ", [$userId]); 
            foreach ($ordersItems as $ordersItem) { 
                $ordersItemsData[] = [
                    "orderId" => (int)$ordersItem["orderitems_id"],
                    "fullName" => $ordersItem["full_name"],
                    "productName" => $ordersItem["product_name"],
                    "productQuantity" => (int)$ordersItem["product_quantity"],
                    "productPrice" => (float)$ordersItem["product_price"],
                    "productImg" => URL_SCHEME . $ordersItem["product_img"],
                    "categoryName" => $ordersItem["category_name"],
                    "orderStatus" => $ordersItem["orders_status"],
                ];
            }

            R::close();
            $this->toJSON(["message" => "get ordersitems..", "ordersItems" => $ordersItemsData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create order items.." . $e->getMessage()], 500);
        }
    }
}