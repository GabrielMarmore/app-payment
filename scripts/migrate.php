<?php

require_once '/var/www/html/vendor/autoload.php';
use App\Services\DbService;

$files = glob('/migrations/*.sql');
sort($files); // garante ordem

$dbService = new DbService();
$pdo = $dbService->getConnection();
foreach ($files as $file) {
    echo "Aplicando migration: " . basename($file) . PHP_EOL;
    $sql = file_get_contents($file);

    try {
        $pdo->exec($sql);
        echo "Migration aplicada com sucesso!" . PHP_EOL;
    } catch (PDOException $e) {
        echo "Erro ao aplicar " . basename($file) . ": " . $e->getMessage() . PHP_EOL;
    }
}
