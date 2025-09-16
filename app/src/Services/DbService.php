<?php

namespace App\Services;

use PDO;
use PDOException;

class DbService
{
    private PDO $pdo;

    public function __construct()
    {
        $host = 'db';
        $db   = 'payments';
        $user = 'user';
        $pass = 'password';
        $dsn  = "pgsql:host=$host;dbname=$db";

        try {
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception("Erro ao conectar no banco: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
