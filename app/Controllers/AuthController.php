<?php

namespace App\Controllers;

use Core\Controller;
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
        $error = $this->getFlash('danger');
        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Handle login form submission.
     */
    public function login(): void
    {
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
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']     = $user['role'];

            \Core\Logger::info('User logged in', ['user_id' => $user['id'], 'username' => $user['username']]);
            \Core\AuditLog::log('login', $user['id'], null, ['username' => $user['username']]);
            $this->redirect('/dashboard');
        } else {
            \Core\Logger::warning('Failed login attempt', ['username' => $username]);
            \Core\AuditLog::log('login_failed', 0, null, ['username' => $username]);
            $this->setFlash('danger', 'Invalid username or password.');
            $this->redirect('/login');
        }
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/dashboard');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->redirect('/dashboard');
        }

        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'unknown';

        // Unset all session variables
        $_SESSION = [];

        // Clear the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly'  => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Strict',
            ]);
        }

        session_destroy();

        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
        exit;
    }
}
