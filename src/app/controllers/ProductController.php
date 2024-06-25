<?php

class ProductController extends BaseController
{
    public function saveProduct()
    {
        try {
            $this->allow("POST");

            $products = R::dispense("products");
            $products->name = conText($_POST["name"]);
            $products->description = conText($_POST["description"]);
            $products->price = $_POST["price"];
            $products->quantity = (int)$_POST["quantity"];
            $products->category_id = $_POST["categoryId"];
            R::store($products);

            if (!empty($_FILES["product_img"])) {
                $filename = file_uploaded("public/uploads/", [
                    "name" => $_FILES["product_img"]["name"],
                    "tmp_name" => $_FILES["product_img"]["tmp_name"]
                ]);

                $attachments = R::dispense("attachments");
                $attachments->filename = $filename;
                $attachments->path = $_FILES["product_img"]["full_path"];
                $attachments->size = $_FILES["product_img"]["size"];
                $attachments->mime_type = "";
                $attachments->extension = $_FILES["product_img"]["type"];
                $attachments->ref_id = $products->id;
                $attachments->ref_table = "products";
                R::store($attachments);
            }

            $productLog = new ProductLogger("logs/save_products.txt");
            $productLog->log("save products: new product id : {$products->id}");
            R::close();

            $this->toJSON(["message" => "create products success.."], 201);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create product: " . $e->getMessage()], 500);
        }
    }

    public function getProducts()
    {
        try {
            $this->allow("GET");

            $products = [];
            $productLog = new ProductLogger("logs/get_products.txt");
            $productsRows = R::getAll(" 
                SELECT p.id, p.name, p.description, p.price, p.quantity, CONCAT('public/uploads/', a.filename) AS product_img, c.name AS category_name
                FROM products AS p
                INNER JOIN attachments AS a ON p.id = a.ref_id
                INNER JOIN categories AS c ON p.id = c.id
                WHERE a.ref_table = ?
                ORDER BY p.created_at DESC", ["products"]);
            foreach ($productsRows as $product) {
                $products[] = [
                    "productId" => (int)$product["id"],
                    "name" => $product["name"],
                    "description" => $product["description"],
                    "price" => (float)$product["price"],
                    "quantity" => (int)$product["quantity"],
                    "categoryName" => $product["category_name"],
                    "productImg" => URL_SCHEME . $product["product_img"],
                ];
                $productLog->log("get products.. " . json_encode($product) . " ");
            }

            R::close();

            $this->toJSON(["message" => "get products..", "products" => $products], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get products.." . $e->getMessage()], 500);
        }
    }

    public function getProduct()
    {
        try {
            $this->allow("GET");

            $productId = query("productId");
            $productCount = R::count("products", "WHERE id = ?", [$productId]);
            if ($productCount) {
                $product = R::getRow(" 
                    SELECT p.id, p.name, p.description, p.price, p.quantity, CONCAT('public/uploads/', a.filename) AS product_img, c.name AS category_name
                    FROM products AS p
                    INNER JOIN attachments AS a ON p.id = a.ref_id
                    INNER JOIN categories AS c ON p.id = c.id
                    WHERE a.ref_table = ? AND p.id = ? 
                    ORDER BY p.created_at DESC", ["products", $productId]);

                $productData = [
                    "productId" => (int)$product["id"],
                    "name" => $product["name"],
                    "description" => $product["description"],
                    "price" => (float)$product["price"],
                    "quantity" => (int)$product["quantity"],
                    "categoryName" => $product["category_name"],
                    "productImg" => URL_SCHEME . $product["product_img"],
                ];

                $productLog = new ProductLogger("logs/get_product_by_id.txt");
                $productLog->log("get products.. " . json_encode($productData) . " ");
                R::close();

                $this->toJSON(["message" => "create products success..", "products" => $productData], 200);
            }
            $this->toJSON(["message" => "products not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get products.." . $e->getMessage()], 500);
        }
    }

    public function updateProduct()
    {
        try {
            $this->allow("POST");

            $productId = query("productId");
            $productCount = R::count("products", "WHERE id = ?", [$productId]);
            if ($productCount) {
                $products = R::load("products", $productId);
                $products->name = conText($_POST["name"]);
                $products->description = conText($_POST["description"]);
                $products->price = $_POST["price"];
                $products->quantity = (int)$_POST["quantity"];
                $products->category_id = $_POST["categoryId"];
                $products->updated_at = date("Y-m-d H:i:s");
                R::store($products);

                if (!empty($_FILES["product_img"])) {
                    $attachment = R::find("attachments", "WHERE ref_table = ? AND ref_id = ?", ["products", $productId]);
                    $attachmentRow = R::exportAll($attachment)[0];

                    unlink("public/uploads/" . $attachmentRow["filename"]);

                    $filename = file_uploaded("public/uploads/", [
                        "name" => $_FILES["product_img"]["name"],
                        "tmp_name" => $_FILES["product_img"]["tmp_name"]
                    ]);

                    $attachments = R::load("attachments", $attachmentRow["id"]);
                    $attachments->filename = $filename;
                    $attachments->path = $_FILES["product_img"]["full_path"];
                    $attachments->size = $_FILES["product_img"]["size"];
                    $attachments->mime_type = "";
                    $attachments->extension = $_FILES["product_img"]["type"];
                    $attachments->ref_id = $productId;
                    $attachments->ref_table = "products";
                    $attachments->updated_at = date("Y-m-d H:i:s");
                    R::store($attachments);
                }

                $productLog = new ProductLogger("logs/update_product_by_id.txt");
                $productLog->log("update products id: {$productId} " . json_encode($products) . " ");
                R::close();
                $this->toJSON(["message" => "update products success.."], 200);
            }

            $this->toJSON(["message" => "products not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to update products.." . $e->getMessage()], 500);
        }
    }

    public function deleteProduct()
    {
        try {
            $this->allow("DELETE");

            $productId = query("productId");
            $productCount = R::count("products", "WHERE id = ?", [$productId]);
            if ($productCount) {
                $attachmentRow = R::findOne("attachments", "WHERE ref_table = ? AND ref_id = ?", ["products", $productId]);
                if (!empty($attachmentRow)) {
                    unlink("public/uploads/" . $attachmentRow["filename"]);
                    R::exec("DELETE FROM attachments WHERE ref_table = ? AND id = ?", ["products", $attachmentRow["id"]]);
                }

                R::trash("products", $productId);

                $productLog = new ProductLogger("logs/delete_product_by_id.txt");
                $productLog->log("delete products id: {$productId}");
                R::close();
                $this->toJSON(["message" => "delete products success.."], 200);
            }
            $this->toJSON(["message" => "products not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to delete products.." . $e->getMessage()], 500);
        }
    }
}
