<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;

class Product
{
  public function __construct(private Database $database) {}

  public function getData(): array
  {
    $pdo = $this->database->getConnection();

    $stmt = $pdo->query("select * from product");

    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}
