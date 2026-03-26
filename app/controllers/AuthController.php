<?php
class AuthController
{
    public function login()
    {
        if ($_POST) {
            $user = $_POST['user'];
            $pass = $_POST['pass'];

            if ($user === 'admin' && $pass === '123') {
                $_SESSION['user'] = $user;
                header('Location: /dashboard');
                exit;
            }

            echo "Login inválido";
        }

        require '../app/views/login.php';
    }
}
