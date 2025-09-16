<?php

namespace Tests;
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\DbService;

class DbTest extends TestCase
{
     private \PDO $pdo;

    protected function setUp(): void
    {
        $dbService = new DbService();
        $this->pdo = $dbService->getConnection();
    }
    public function testConnection()
    {
        $stmt = $this->pdo->query("SELECT NOW() as current_time");
        $row = $stmt->fetch();

        $this->assertNotEmpty($row['current_time'], "Banco de dados não retornou hora atual");
    }

     public function testTablesWereCreated()
    {
        $expectedTables = ['users', 'transactions'];

        foreach ($expectedTables as $table) {
            $stmt = $this->pdo->prepare("SELECT to_regclass(:table) AS exists");
            $stmt->execute(['table' => $table]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->assertNotNull(
                $result['exists'],
                "A tabela '{$table}' não foi encontrada no banco."
            );
        }
    }
}
