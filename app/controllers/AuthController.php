<?php
class AuthController
{
    public function login()
    {
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
            } catch (PDOException $exception) {
                http_response_code(500);
                echo 'Erro ao conectar com o banco de dados.';
                return;
            }

            echo "Login inválido";
        }

        Render::view('login');
    }
}
