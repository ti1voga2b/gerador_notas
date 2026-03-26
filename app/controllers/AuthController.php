<?php
class AuthController
{
    public function login()
    {
        $error = null;

        if ($_POST) {
            $user = trim($_POST['user'] ?? '');
            $pass = $_POST['pass'] ?? '';

            try {
                $pdo = Database::connection();
                $table = $_ENV['AUTH_TABLE'] ?? 'users';
                $userColumn = $_ENV['AUTH_USER_COLUMN'] ?? 'user';
                $passColumn = $_ENV['AUTH_PASS_COLUMN'] ?? 'password';
                $idColumn = $_ENV['AUTH_ID_COLUMN'] ?? 'id';

                $stmt = $pdo->prepare(
                    "SELECT {$idColumn}, {$userColumn}, {$passColumn} FROM {$table} WHERE {$userColumn} = :user LIMIT 1"
                );
                $stmt->execute(['user' => $user]);
                $authUser = $stmt->fetch();

                $isValidPassword = $authUser
                    && (
                        password_verify($pass, $authUser[$passColumn])
                        || $pass === $authUser[$passColumn]
                    );

                if ($isValidPassword) {
                    $_SESSION['user'] = $authUser[$userColumn];
                    $_SESSION['user_id'] = $authUser[$idColumn] ?? null;
                    header('Location: ' . url('/dashboard'));
                    exit;
                }
            } catch (Throwable $exception) {
                error_log(sprintf(
                    '[database] %s | host=%s port=%s db=%s user=%s',
                    $exception->getMessage(),
                    $_ENV['DB_HOST'] ?? '',
                    $_ENV['DB_PORT'] ?? '',
                    $_ENV['DB_NAME'] ?? '',
                    $_ENV['DB_USER'] ?? ''
                ));

                http_response_code(500);
                echo 'Erro ao conectar com o banco de dados.';
                return;
            }

            $error = 'Login inválido.';
        }

        Render::view('login', [
            'error' => $error,
        ]);
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        header('Location: ' . url('/'));
        exit;
    }
}
