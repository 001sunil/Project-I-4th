<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use App\Models\User;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show the login form.
     */
    public function showLogin(): void
    {
        Middleware::guest();
        $error = $this->getFlash('danger');
        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Handle login form submission.
     */
    public function login(): void
    {
        Middleware::guest();

        if (!$this->isPost()) {
            $this->redirect('/login');
        }

        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');

        if (empty($username) || empty($password)) {
            $this->setFlash('danger', 'Please enter both username and password.');
            $this->redirect('/login');
        }

        $user = $this->userModel->authenticate($username, $password);

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']     = $user['role'];
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('danger', 'Invalid username or password.');
            $this->redirect('/login');
        }
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        session_destroy();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
        exit;
    }
}
