<?php

class CategoriesController extends BaseController
{
    public function saveCategories()
    {
        try {
            $this->allow("POST");

            $body = $this->ajaxRequest();

            $category = R::dispense("categories");
            $category->name = conText($body["name"]);
            $category->description = conText($body["description"]);
            R::store($category);

            $categoryLog = new CategoriesLogger("logs/save_categories.txt");
            $categoryLog->log("save categories: new categories id : {$category->id}");
            R::close();

            $this->toJSON(["message" => "create categories success.."], 201);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create categories: " . $e->getMessage()], 500);
        }
    }

    public function getCategories()
    {
        try {
            $this->allow("GET");

            $categorieData = [];
            $categories = R::getAll("SELECT id, name, description FROM categories ORDER BY created_at DESC");
            foreach ($categories as $category) {
                $categorieData[] = [
                    "categoryId" => (int)$category["id"],
                    "name" => $category["name"],
                    "description" => $category["description"],
                ];
            }

            $categoryLog = new CategoriesLogger("logs/get_categories.txt");
            $categoryLog->log("get categories ..");
            R::close();

            $this->toJSON(["message" => "get categories..", "categories" => $categorieData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get categories: " . $e->getMessage()], 500);
        }
    }

    public function getCategorie()
    {
        try {
            $this->allow("GET");

            $categoryId = query("categoryId");
            $categorieData = [];
            $categories = R::findOne("categories", "WHERE id = ?", [$categoryId]);

            $categorieData = [
                "categoryId" => (int)$categories["id"],
                "name" => $categories["name"],
                "description" => $categories["description"],
            ];

            $categoryLog = new CategoriesLogger("logs/get_category.txt");
            $categoryLog->log("get categories ..");
            R::close();
            $this->toJSON(["message" => "get categories..", "categories" => $categorieData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get categories: " . $e->getMessage()], 500);
        }
    }

    public function updateCategorie()
    {
        try {
            $this->allow("PUT");
            $categoryId = query("categoryId");
            $body = $this->ajaxRequest();

            $categoriesCount = R::count("categories", "WHERE id = ?", [$categoryId]);
            if ($categoriesCount) {
                $category = R::load("categories", $categoryId);
                $category->name = conText($body["name"]);
                $category->description = conText($body["description"]);
                $category->updated_at = date("Y-m-d H:i:s");
                R::store($category);

                $categoryLog = new CategoriesLogger("logs/update_category.txt");
                $categoryLog->log("update categories id: {$categoryId}");
                R::close();
                $this->toJSON(["message" => "update categories.."], 200);
            }
            $this->toJSON(["message" => "categories not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to update categories: " . $e->getMessage()], 500);
        }
    }

    public function deleteCategorie()
    {
        try {
            $this->allow("DELETE");
            $categoryId = query("categoryId");
            $categoriesCount = R::count("categories", "WHERE id = ?", [$categoryId]);
            if ($categoriesCount) { 
                R::trash("categories", $categoryId);

                $productLog = new ProductLogger("logs/delete_categories_by_id.txt");
                $productLog->log("delete categories id: {$categoryId}");
                R::close();
                $this->toJSON(["message" => "delete categories.."], 200);
            }
            $this->toJSON(["message" => "categories not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to delete categories: " . $e->getMessage()], 500);
        }
    }
}
