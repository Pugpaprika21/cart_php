<?php

class OrdersController extends BaseController
{
    public function saveOrders()
    {
        try {
            $this->allow("POST");

            $body = $this->ajaxRequest();

            $productId = conText($body["product_id"]);
            $quantity = (int)conText($body["quantity"]);

            $product = R::load('products', $productId);
            if ($product->id === 0) {
                $this->toJSON(["message" => "product not found."], 200);
            }

            if ($product->quantity < $quantity) {
                $this->toJSON(["message" => "not enough stock available."], 200);
            }

            $userId = (int)conText($body["user_id"]);

            $orders = R::dispense("orders");
            $orders->user_id = $userId;
            $orders->product_id = (int)$productId;
            $orders->quantity = $quantity;
            $orders->price = 0.00;
            $orders->status = "pending";
            R::store($orders);

            $product->quantity -= $quantity;
            R::store($product);

            $orderItems = R::dispense("orderitems");
            $orderItems->user_id = $userId;
            $orderItems->order_id = $orders->id;
            $orderItems->product_id = $productId;
            R::store($orderItems);
            R::close();

            $this->toJSON(["message" => "create orders success.."], 201);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create orders.." . $e->getMessage()], 500);
        }
    }

    public function getOrders()
    {
        try {
            $this->allow("GET");

            $filter = "";
            $orderStatus = query("orderStatus");
            if ($orderStatus) {
                $filter .= "o.status = '{$orderStatus}' AND ";
            }

            $ordersData = [];
            $orders = R::getAll("
                SELECT 
                    o.id AS order_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                    p.name AS product_name,
                    o.quantity AS product_quantity,
                    p.price AS product_price,
                    c.name AS category_name,
                    o.status AS orders_status,
                    CONCAT('public/uploads/', a.filename) AS product_img
                FROM orders AS o 
                INNER JOIN products AS p ON o.product_id = p.id
                INNER JOIN users AS u ON o.user_id = u.id
                INNER JOIN categories AS c ON p.category_id = c.id
                INNER JOIN attachments AS a ON p.id = a.ref_id
                WHERE {$filter} DATE(o.created_at) = CURRENT_DATE 
                ORDER BY o.created_at DESC
            ");
            foreach ($orders as $order) {
                $ordersData[] = [
                    "orderId" => (int)$order["order_id"],
                    "fullName" => $order["full_name"],
                    "productName" => $order["product_name"],
                    "productQuantity" => (int)$order["product_quantity"],
                    "productPrice" => (float)$order["product_price"],
                    "productImg" => URL_SCHEME . $order["product_img"],
                    "categoryName" => $order["category_name"],
                    "orderStatus" => $order["orders_status"],
                ];
            }

            R::close();
            $this->toJSON(["message" => "get orders success..", "orders" => $ordersData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create orders.." . $e->getMessage()], 500);
        }
    }

    public function getOrder()
    {
        try {
            $this->allow("GET");

            $orderId = query("orderId");

            $filter = "";
            $orderStatus = query("orderStatus");
            if ($orderStatus) {
                $filter .= "o.status = '{$orderStatus}' AND ";
            }

            $orderCount = R::count("orders", "WHERE id = ?", [$orderId]);
            if ($orderCount) {
                $order = R::getRow("
                    SELECT 
                        o.id AS order_id,
                        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                        p.name AS product_name,
                        o.quantity AS product_quantity,
                        p.price AS product_price,
                        c.name AS category_name,
                        o.status AS orders_status,
                        CONCAT('public/uploads/', a.filename) AS product_img
                    FROM orders AS o 
                    INNER JOIN products AS p ON o.product_id = p.id
                    INNER JOIN users AS u ON o.user_id = u.id
                    INNER JOIN categories AS c ON p.category_id = c.id
                    INNER JOIN attachments AS a ON p.id = a.ref_id
                    WHERE {$filter} o.id = ?
                ", [$orderId]);

                $ordersData = [
                    "orderId" => (int)$order["order_id"],
                    "fullName" => $order["full_name"],
                    "productName" => $order["product_name"],
                    "productQuantity" => (int)$order["product_quantity"],
                    "productPrice" => (float)$order["product_price"],
                    "productImg" => URL_SCHEME . $order["product_img"],
                    "categoryName" => $order["category_name"],
                    "orderStatus" => $order["orders_status"],
                ];

                R::close();
                $this->toJSON(["message" => "get orders success..", "order" => $ordersData], 200);
            }

            $this->toJSON(["message" => "order not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create order.." . $e->getMessage()], 500);
        }
    }

    public function getOrdersByUser()
    {
        try {
            $this->allow("GET");

            $userId = query("userId");

            $filter = "";
            $orderStatus = query("orderStatus");
            if ($orderStatus) {
                $filter .= "o.status = '{$orderStatus}' AND ";
            }

            $orderCount = R::count("orders", "WHERE user_id = ?", [$userId]);
            if ($orderCount) {
                $userOrders = R::getAll("
                    SELECT 
                        o.id AS order_id,
                        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                        p.name AS product_name,
                        p.price AS product_price,
                        o.quantity AS product_quantity,
                        c.name AS category_name,
                        o.status AS orders_status,
                        CONCAT('public/uploads/', a.filename) AS product_img
                    FROM orders AS o 
                    INNER JOIN products AS p ON o.product_id = p.id
                    INNER JOIN users AS u ON o.user_id = u.id
                    INNER JOIN categories AS c ON p.category_id = c.id
                    INNER JOIN attachments AS a ON p.id = a.ref_id
                    WHERE {$filter} o.user_id = ? AND DATE(o.created_at) = CURRENT_DATE 
                    ORDER BY o.created_at DESC
                ", [$userId]);
                $ordersTotal = 0;
                foreach ($userOrders as $order) {
                    $productTotalPrice = (float)$order["product_price"] * (int)$order["product_quantity"];
                    $ordersTotal += $productTotalPrice;

                    $ordersData[] = [
                        "orderId" => (int)$order["order_id"],
                        "fullName" => $order["full_name"],
                        "productName" => $order["product_name"],
                        "productQuantity" => (int)$order["product_quantity"],
                        "productPrice" => (float)$order["product_price"],
                        "productImg" => URL_SCHEME . $order["product_img"],
                        "categoryName" => $order["category_name"],
                        "orderStatus" => $order["orders_status"],
                    ];
                }
                $ordersTotal = round($ordersTotal * 1.07, 2);

                R::close();
                $this->toJSON(["message" => "get orders success..", "order" => $ordersData, "ordersTotal" => $ordersTotal], 200);
            }

            $this->toJSON(["message" => "order not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create order.." . $e->getMessage()], 500);
        }
    }

    public function updateOrderByUser()
    {
        try {
            $this->allow("PUT");

            $userId = query("userId");
            $orderId = query("orderId");

            $body = $this->ajaxRequest();
            $orderRow = R::getRow("SELECT id, product_id, quantity, status FROM orders WHERE status = 'pending' AND user_id = ? AND id = ?", [$userId, $orderId]);
            if (!empty($orderRow)) {
                $productId = (int)$orderRow["product_id"];
                $currentQuantity = (int)$orderRow["quantity"];
                $newQuantity = (int)conText($body["quantity"]);

                if ($newQuantity <= 0) {
                    $this->toJSON(["message" => "quantity must be a positive integer greater than zero."], 400);
                }

                $product = R::load("products", $productId);
                $availableQuantity = (int)$product->quantity;

                if ($newQuantity !== $currentQuantity) {
                    if ($newQuantity <= $availableQuantity) {
                        $quantityDifference = $newQuantity - $currentQuantity;

                        R::begin();

                        try {
                            $order = R::load("orders", $orderRow["id"]);
                            $order->quantity = $newQuantity;
                            R::store($order);
                            $product->quantity -= $quantityDifference;
                            R::store($product);

                            R::commit();

                            $orderRow["quantity"] = $newQuantity;

                            $this->toJSON(["message" => "order quantity updated successfully."], 200);
                        } catch (Exception $ex) {
                            R::rollback();
                            $this->toJSON(["message" => "failed to update order: " . $ex->getMessage()], 500);
                        }
                    } else {
                        $this->toJSON(["message" => "not enough quantity available.", "availableQuantity" => $availableQuantity], 400);
                    }
                } else {
                    $this->toJSON(["message" => "quantity is already up to date."], 200);
                }
            } else {
                $this->toJSON(["message" => "order not found or not pending."], 404);
            }
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to update order: " . $e->getMessage()], 500);
        }
    }

    public function buyOrdersByUser()
    {
        try {
            $this->allow("POST");

            $body = $this->ajaxRequest();

            $userId = conText($body["userId"]);
            $orders = !empty($body["orders"]) ? $body["orders"] : [];
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $orderId = conText($order["orderId"]);
                    R::exec("UPDATE orders SET status = 'completed' WHERE status = 'pending' AND user_id = ? AND id = ?", [$userId, $orderId]);
                }
                $this->toJSON(["message" => "buy orders successfully."], 200);
            } else {
                $this->toJSON(["message" => "no orders found to buy."], 404);
            }
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to buy orders: " . $e->getMessage()], 500);
        }
    }

    public function deleteOrderByUser()
    {
        try {
            $this->allow("DELETE");

            $userId = query("userId");
            $orderId = query("orderId");

            $order = R::findOne("orders", "user_id = ? AND id = ? AND status = ?", [$userId, $orderId, "pending"]);
            if ($order) {
                R::begin();

                try {
                    $productId = (int)$order->product_id;
                    $quantityToCancel = (int)$order->quantity;

                    $order->status = "canceled";
                    R::store($order);

                    $product = R::load("products", $productId);
                    $product->quantity += $quantityToCancel;
                    R::store($product);
                    R::commit();
                    R::close();

                    $this->toJSON(["message" => "order canceled successfully."], 200);
                } catch (Exception $ex) {
                    R::rollback();
                    $this->toJSON(["message" => "failed to cancel order: " . $ex->getMessage()], 500);
                }
            } else {
                $this->toJSON(["message" => "order not found or already canceled."], 404);
            }
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to cancel order: " . $e->getMessage()], 500);
        }
    }
}
