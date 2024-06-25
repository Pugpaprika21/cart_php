<?php

class UsersController extends BaseController
{
    public function saveUsers()
    {
        try {
            $this->allow("POST");

            $body = $this->ajaxRequest();

            $users = R::dispense("users");
            $users->username = conText($body["username"]);
            $users->email = conText($body["email"]);
            $users->password_hash = password_hash(conText($body["password_hash"]), PASSWORD_BCRYPT, ["cost" => 10]);
            $users->first_name = conText($body["first_name"]);
            $users->last_name = conText($body["last_name"]);
            $users->date_of_birth = conText($body["date_of_birth"]);
            $users->address = conText($body["address"]);
            $users->phone_number = conText($body["phone_number"]);
            $users->last_name = conText($body["last_name"]);
            R::store($users);
            R::close();

            $this->toJSON(["message" => "create users success.."], 201);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to create users: " . $e->getMessage()], 500);
        }
    }

    public function getUsers()
    {
        try {
            $this->allow("GET");

            $usersData = [];
            $users = R::exportAll(R::findAll("users", "ORDER BY created_at DESC"));
            foreach ($users as $user) {
                $usersData[] = [
                    "userId" => (int)$user["id"],
                    "username" => $user["username"],
                    "email" => $user["email"],
                    "passwordHash" => $user["password_hash"],
                    "fullName" => $user["first_name"] . " " . $user["last_name"],
                    "dateOfBirth" => $user["date_of_birth"],
                    "address" => $user["address"],
                    "phoneNumber" => $user["phone_number"],
                ];
            }

            R::close();
            $this->toJSON(["message" => "get users success..", "users" => $usersData], 200);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get users: " . $e->getMessage()], 500);
        }
    }

    public function getUser()
    {
        try {
            $this->allow("GET");

            $userId = query("userId");
            $userCount = R::count("users", "WHERE id = ?", [$userId]);
            if ($userCount) {
                $user = R::findOne("users", "WHERE id = ?", [$userId]);
                $usersData = [
                    "userId" => (int)$user["id"],
                    "username" => $user["username"],
                    "email" => $user["email"],
                    "passwordHash" => $user["password_hash"],
                    "fullName" => $user["first_name"] . " " . $user["last_name"],
                    "dateOfBirth" => $user["date_of_birth"],
                    "address" => $user["address"],
                    "phoneNumber" => $user["phone_number"],
                ];

                R::close();
                $this->toJSON(["message" => "get user success..", "user" => $usersData], 200);
            }
            $this->toJSON(["message" => "users not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to get user: " . $e->getMessage()], 500);
        }
    }

    public function updateUsers()
    {
        try {
            $this->allow("PUT");
     
            $body = $this->ajaxRequest();

            $userId = query("userId");
            $userCount = R::count("users", "WHERE id = ?", [$userId]);
            if ($userCount) {
                $users = R::load("users", $userId);
                $users->username = conText($body["username"]);
                $users->email = conText($body["email"]);
                $users->password_hash = password_hash(conText($body["password_hash"]), PASSWORD_BCRYPT, ["cost" => 10]);
                $users->first_name = conText($body["first_name"]);
                $users->last_name = conText($body["last_name"]);
                $users->date_of_birth = conText($body["date_of_birth"]);
                $users->address = conText($body["address"]);
                $users->phone_number = conText($body["phone_number"]);
                $users->last_name = conText($body["last_name"]);
                R::store($users);
                R::close();

                $this->toJSON(["message" => "update users success.."], 201);
            }
            $this->toJSON(["message" => "users not found.."], 204);
        } catch (Exception $e) {
            $this->toJSON(["message" => "failed to update users: " . $e->getMessage()], 500);
        }
    }

    public function deleteUsers()
    {
        try {
            $this->allow("DELETE");
            
            $userId = query("userId");
            $userCount = R::count("users", "WHERE id = ?", [$userId]);
            if ($userCount) { 
                R::trash("users", $userId);
                R::close();
                $this->toJSON(["message" => "users success.."], 201); 
            }
            $this->toJSON(["message" => "users not found.."], 204);
        } catch (Exception $e) { 
            $this->toJSON(["message" => "failed to users: " . $e->getMessage()], 500);
        }
    }
}
