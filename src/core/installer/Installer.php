<?php

namespace core\installer;

use core\App;
use core\exception\AppException;
use PDO;
use PDOException;

/**
 *
 */
class Installer
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = App::$db;
    }

    /**
     * @param $table
     * @return bool
     */
    public function checkInstalled($table): bool
    {
        try {
            $query = $this->pdo->prepare("SELECT 1 FROM {$table} LIMIT 1");
            $result = $query->execute();
        } catch (PDOException) {
            return false;
        }

        return $result ?? true;
    }

    /**
     * @param $table
     * @return void
     * @throws AppException
     */
    public function install($table): void
    {
        try {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "CREATE TABLE `{$table}` (
  `id` int NOT NULL,
  `name` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_completed` tinyint(1) NOT NULL,
  `system` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            $keysSql = "ALTER TABLE `{$table}`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_index` (`system`,`system_id`);";

            $keysAutoIncrement = "ALTER TABLE `{$table}`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;";

            $this->pdo->exec($sql);
            $this->pdo->exec($keysSql);
            $this->pdo->exec($keysAutoIncrement);

        } catch (PDOException $e) {
            throw new AppException('Can \'t install the app: ' . $e->getMessage());
        }
    }
}