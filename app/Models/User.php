<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find a user by username.
     */
    public function findByUsername(string $username): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE username = ?",
            [$username],
            's'
        );
    }

    /**
     * Authenticate a user with username and password.
     * Checks is_active and account locking.
     * Returns user data on success, null on failure.
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if (!$user) {
            return null;
        }

        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return null; // Account is locked
        }

        // Check if account is active
        if (!$user['is_active']) {
            return null;
        }

        if (password_verify($password, $user['password'])) {
            // Reset login attempts on success
            $this->db->query(
                "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?",
                [$user['id']],
                'i'
            );
            return $user;
        }

        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $lockUntil = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;

        $this->db->query(
            "UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?",
            [$attempts, $lockUntil, $user['id']],
            'isi'
        );

        return null;
    }

    /**
     * Create a new user with hashed password.
     */
    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }

    /**
     * Update user, optionally updating the password.
     */
    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Get all active users ordered by name.
     */
    public function getAllUsers(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM users WHERE is_active = 1 ORDER BY full_name ASC"
        );
    }

    /**
     * Get avatar path for a user.
     */
    public function getAvatarUrl(?string $avatar): ?string
    {
        if ($avatar && file_exists(__DIR__ . '/../../uploads/avatars/' . $avatar)) {
            return '/uploads/avatars/' . htmlspecialchars($avatar);
        }
        return null;
    }
}
