<?php

declare(strict_types=1);

/**
 * Acceso a base de datos mediante PDO y patrón Singleton.
 */
final class Database
{
    private static ?self $instance = null;

    private PDO $connection;

    private function __construct()
    {
        $host = $this->getEnvValue('DB_HOST');
        $name = $this->getEnvValue('DB_NAME');
        $user = $this->getEnvValue('DB_USER');
        $pass = $this->getEnvValue('DB_PASS', true);

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $name);

        try {
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'No fue posible establecer la conexión con la base de datos.',
                0,
                $exception
            );
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function getEnvValue(string $key, bool $allowEmpty = false): string
    {
        $value = getenv($key);

        if ($value === false || $value === null) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }

        $value = is_string($value) ? trim($value) : '';

        if ($value === '' && !$allowEmpty) {
            throw new RuntimeException(sprintf('La variable de entorno %s no está definida.', $key));
        }

        return $value;
    }
}
