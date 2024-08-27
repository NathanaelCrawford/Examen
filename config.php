
<?php

class DatabaseConnection
{
    private $host = 'localhost';
    private $dbname = 'schooldatabase';
    private $username = 'root';
    private $password = '';
    private $pdo;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->handleError($e->getMessage());
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    private function handleError($message)
    {
        //
        die('Verbinding niet gelukt: ' . $message);
    }
}

// DatabaseConnection Class
$dbConnection = new DatabaseConnection();
$pdo = $dbConnection->getPdo();

if ($pdo) {
    echo '<br>';
}
?>
