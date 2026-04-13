<?php

declare(strict_types=1);

/**
 * Servicio de autenticación de usuarios.
 */
final class Auth
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
    }

    public function login(string $email, string $password): bool
    {
        $email = trim($email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return false;
        }

        $sql = 'SELECT id, nombre, email, password_hash, rol, activo
                FROM usuarios
                WHERE email = :email
                LIMIT 1';

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute(['email' => $email]);
            $usuario = $statement->fetch();
        } catch (PDOException) {
            return false;
        }

        if ($usuario === false || (int) $usuario['activo'] !== 1) {
            return false;
        }

        if (!password_verify($password, (string) $usuario['password_hash'])) {
            return false;
        }

        $this->startSession($usuario);

        return true;
    }

    public function startSession(array $usuario): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);

        $id = (int) ($usuario['id'] ?? 0);
        $nombre = trim((string) ($usuario['nombre'] ?? ''));
        $email = trim((string) ($usuario['email'] ?? ''));
        $rol = trim((string) ($usuario['rol'] ?? ''));

        $_SESSION['id'] = $id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['email'] = $email;
        $_SESSION['rol'] = $rol;

        // Alias de compatibilidad para partes de la interfaz existentes.
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = $rol;
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();

        header('Location: /login.php');
        exit;
    }

    public function isLoggedIn(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return isset($_SESSION['id']) || isset($_SESSION['user_id']);
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
}
