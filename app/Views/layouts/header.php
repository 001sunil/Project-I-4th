<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediStock — Medicine Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo \Core\View::url('/assets/css/style.css?v=7'); ?>">
</head>
<body class="role-<?php echo htmlspecialchars($_SESSION['role'] ?? 'staff'); ?>">
    <!-- Mobile sidebar toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-pills"></i> MediStock
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-section">Main</li>
            <li><a href="<?php echo \Core\View::url('/dashboard'); ?>" class="<?php echo \Core\View::currentPage() === 'dashboard' ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard</a></li>

            <li class="sidebar-section">Inventory</li>
            <li><a href="<?php echo \Core\View::url('/medicines'); ?>" class="<?php echo in_array(\Core\View::currentPage(), ['medicines']) ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-capsules"></i></span> Medicines</a></li>
            <li><a href="<?php echo \Core\View::url('/medicines/create'); ?>" class="<?php echo \Core\View::currentPage() === 'create' && str_contains($_SERVER['REQUEST_URI'], 'medicines') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-plus-circle"></i></span> Add Medicine</a></li>
            <li><a href="<?php echo \Core\View::url('/stock-adjustments'); ?>" class="<?php echo \Core\View::currentPage() === 'stock-adjustments' ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-sliders-h"></i></span> Stock Adjustment</a></li>

            <li class="sidebar-section">Sales</li>
            <li><a href="<?php echo \Core\View::url('/sales/create'); ?>" class="<?php echo \Core\View::currentPage() === 'create' && str_contains($_SERVER['REQUEST_URI'], 'sales') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-shopping-cart"></i></span> New Sale</a></li>
            <li><a href="<?php echo \Core\View::url('/sales'); ?>" class="<?php echo \Core\View::currentPage() === 'sales' || \Core\View::currentPage() === 'index' && str_contains($_SERVER['REQUEST_URI'], 'sales') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-receipt"></i></span> Sales History</a></li>

            <li class="sidebar-section">Reports</li>
            <li><a href="<?php echo \Core\View::url('/reports/low-stock'); ?>" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'low-stock') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-exclamation-triangle"></i></span> Low Stock</a></li>
            <li><a href="<?php echo \Core\View::url('/reports/expiry'); ?>" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'reports/expiry') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-clock"></i></span> Expiry Alerts</a></li>
            <li><a href="<?php echo \Core\View::url('/reports/prescriptions'); ?>" class="<?php echo str_contains($_SERVER['REQUEST_URI'], 'prescriptions') ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-file-medical"></i></span> Prescription Audit</a></li>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') : ?>
            <li class="sidebar-section">Admin</li>
            <li><a href="<?php echo \Core\View::url('/users'); ?>" class="<?php echo \Core\View::currentPage() === 'users' ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-users-cog"></i></span> Users</a></li>
            <li><a href="<?php echo \Core\View::url('/settings'); ?>" class="<?php echo \Core\View::currentPage() === 'settings' ? 'active' : ''; ?>"><span class="icon"><i class="fas fa-cog"></i></span> Settings</a></li>
            <?php endif; ?>

            <li>
                <form method="POST" action="<?php echo \Core\View::url('/logout'); ?>" class="logout-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <button type="submit" class="logout-link"><span class="icon"><i class="fas fa-sign-out-alt"></i></span> Logout</button>
                </form>
            </li>
        </ul>
        <div class="sidebar-footer">
            <?php
            // Fetch avatar for sidebar display
            $sidebarAvatar = null;
            if (isset($_SESSION['user_id'])) {
                $userModel = new \App\Models\User();
                $avatarRow = $userModel->find((int) $_SESSION['user_id']);
                $sidebarAvatar = $userModel->getAvatarUrl($avatarRow['avatar'] ?? null);
            }
            ?>
            <?php if ($sidebarAvatar): ?>
                <img src="<?php echo $sidebarAvatar; ?>" alt="Avatar" class="user-avatar-img">
            <?php else: ?>
                <div class="user-avatar"><?php echo strtoupper(htmlspecialchars(substr($_SESSION['full_name'] ?? 'U', 0, 1))); ?></div>
            <?php endif; ?>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></div>
                <div class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'guest')); ?></div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h1><?php echo htmlspecialchars($pageTitle ?? 'MediStock'); ?></h1>
            </div>
            <div class="topbar-right">
                <?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>
            </div>
        </div>
        <div class="container">
