<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * List all users (admin only).
     */
    public function index(): void
    {
        $users = $this->userModel->getAllUsers();
        $flashSuccess = $this->getFlash('success');
        $flashDanger = $this->getFlash('danger');

        require __DIR__ . '/../Views/users/index.php';
    }

    /**
     * Show the create user form (admin only).
     */
    public function create(): void
    {
        $error = $this->getFlash('danger');
        require __DIR__ . '/../Views/users/create.php';
    }

    /**
     * Store a new user (admin only).
     */
    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/users/create');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect('/users/create');
        }

        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');
        $fullName = trim($this->input('full_name', ''));
        $role = $this->input('role', 'staff');

        if (empty($username) || empty($password) || empty($fullName)) {
            $this->setFlash('danger', 'Please fill in all required fields.');
            $this->redirect('/users/create');
        }

        if (strlen($password) < 6) {
            $this->setFlash('danger', 'Password must be at least 6 characters.');
            $this->redirect('/users/create');
        }

        if ($this->userModel->findByUsername($username)) {
            $this->setFlash('danger', 'Username already exists.');
            $this->redirect('/users/create');
        }

        if (!in_array($role, ['admin', 'staff'])) {
            $role = 'staff';
        }

        $avatar = $this->handleAvatarUpload();

        $this->userModel->createUser([
            'username'  => $username,
            'password'  => $password,
            'full_name' => $fullName,
            'role'      => $role,
            'avatar'    => $avatar,
        ]);

        \Core\Logger::info('User created', ['username' => $username, 'role' => $role]);
        \Core\AuditLog::log('user_created', (int) ($_SESSION['user_id'] ?? 0), null, ['username' => $username, 'role' => $role]);
        $this->setFlash('success', 'User created successfully!');
        $this->redirect('/users');
    }

    /**
     * Show the edit user form (admin only).
     */
    public function edit(string $id): void
    {
        $user = $this->userModel->find((int) $id);
        if (!$user) {
            $this->setFlash('danger', 'User not found.');
            $this->redirect('/users');
        }

        $error = $this->getFlash('danger');
        require __DIR__ . '/../Views/users/edit.php';
    }

    /**
     * Update an existing user (admin only).
     */
    public function update(string $id): void
    {
        if (!$this->isPost()) {
            $this->redirect("/users/{$id}/edit");
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request. Please try again.');
            $this->redirect("/users/{$id}/edit");
        }

        $fullName = trim($this->input('full_name', ''));
        $role = $this->input('role', 'staff');
        $password = $this->input('password', '');

        if (empty($fullName)) {
            $this->setFlash('danger', 'Full name is required.');
            $this->redirect("/users/{$id}/edit");
        }

        if (!empty($password) && strlen($password) < 6) {
            $this->setFlash('danger', 'Password must be at least 6 characters.');
            $this->redirect("/users/{$id}/edit");
        }

        if (!in_array($role, ['admin', 'staff'])) {
            $role = 'staff';
        }

        $updateData = [
            'full_name' => $fullName,
            'role'      => $role,
        ];

        if (!empty($password)) {
            $updateData['password'] = $password;
        }

        $avatar = $this->handleAvatarUpload();
        if ($avatar) {
            $existing = $this->userModel->find((int) $id);
            if ($existing && !empty($existing['avatar'])) {
                $oldPath = __DIR__ . '/../../uploads/avatars/' . $existing['avatar'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $updateData['avatar'] = $avatar;
        }

        $this->userModel->updateUser((int) $id, $updateData);

        \Core\Logger::info('User updated', ['user_id' => $id]);
        \Core\AuditLog::log('user_updated', (int) ($_SESSION['user_id'] ?? 0), null, ['user_id' => $id]);
        $this->setFlash('success', 'User updated successfully!');
        $this->redirect('/users');
    }

    /**
     * Delete a user (admin only).
     */
    public function destroy(string $id): void
    {
        if (!$this->isPost()) {
            $this->redirect('/users');
        }

        if (!$this->validateCsrf($this->input('csrf_token', ''))) {
            $this->setFlash('danger', 'Invalid request.');
            $this->redirect('/users');
        }

        if ((int) $id === (int) $_SESSION['user_id']) {
            $this->setFlash('danger', 'You cannot delete your own account.');
            $this->redirect('/users');
        }

        $user = $this->userModel->find((int) $id);
        if ($user && !empty($user['avatar'])) {
            $avatarPath = __DIR__ . '/../../uploads/avatars/' . $user['avatar'];
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }

        $this->userModel->delete((int) $id);
        \Core\Logger::info('User deleted', ['user_id' => $id]);
        \Core\AuditLog::log('user_deleted', (int) ($_SESSION['user_id'] ?? 0), ['user_id' => $id], null);
        $this->setFlash('success', 'User deleted successfully.');
        $this->redirect('/users');
    }

    /**
     * Handle avatar file upload. Returns filename on success, null on failure/skip.
     */
    private function handleAvatarUpload(): ?string
    {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['avatar'];

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->setFlash('danger', 'Avatar must be less than 2MB.');
            $this->redirect($_SERVER['REQUEST_URI']);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            $this->setFlash('danger', 'Avatar must be a JPG, PNG, or GIF image.');
            $this->redirect($_SERVER['REQUEST_URI']);
        }

        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $filename = 'avatar_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/avatars/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return $filename;
        }

        return null;
    }
}
