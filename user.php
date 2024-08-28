<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            return true;
        } else {
            return false;
        }
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getRoleId()
    {
        return $_SESSION['role_id'] ?? null;
    }

    public function logout()
    {
        session_unset();
        session_destroy();
    }

    // Methode om een nieuwe gebruiker aan te maken
    public function createUser($username, $email, $password, $role_id, $class_id = null)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role_id, class_id) VALUES (:username, :email, :password, :role_id, :class_id)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindParam(':class_id', $class_id);

        return $stmt->execute();
    }

    // Methode om een gebruiker te verwijderen
    public function deleteUser($user_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    }

    // Methode om gebruikersinformatie bij te werken
    public function updateUser($user_id, $username, $email, $role_id, $class_id = null)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, email = :email, role_id = :role_id, class_id = :class_id WHERE id = :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':id', $user_id);

        return $stmt->execute();
    }

    // Methode om een lijst van alle docenten op te halen
    public function getAllTeachers()
    {
        $stmt = $this->pdo->query("SELECT * FROM users WHERE role_id = 2");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Methode om een lijst van alle studenten op te halen
    public function getAllStudents()
    {
        $stmt = $this->pdo->query("SELECT u.*, k.class_name FROM users u LEFT JOIN klassen k ON u.class_id = k.id WHERE u.role_id = 4");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Methode om een enkele gebruiker op te halen
    public function getUserById($user_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Methode om een lijst van alle klassen op te halen
    public function getAllClasses()
    {
        $stmt = $this->pdo->query("SELECT * FROM klassen");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
