<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MediStock</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo \Core\View::url('/favicon.svg'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo \Core\View::url('/assets/css/style.css?v=7'); ?>">
</head>
<body>
    <div class="login-page">
        <div class="login-left">
            <div class="login-brand">
                <div class="login-brand-icon"><i class="fas fa-pills"></i></div>
                <p>Medicine Inventory Management System</p>
            </div>
            <div class="login-features">
                <div class="login-feature">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Track inventory in real-time</span>
                </div>
                <div class="login-feature">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales analytics & reports</span>
                </div>
                <div class="login-feature">
                    <i class="fas fa-bell"></i>
                    <span>Expiry & low stock alerts</span>
                </div>
                <div class="login-feature">
                    <i class="fas fa-shield-halved"></i>
                    <span>Prescription audit trail</span>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <div class="login-form-header">
                    <h1>Welcome back</h1>
                    <p>Sign in to your account</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo \Core\View::url('/login'); ?>">
                    <?php \Core\View::csrfField(); ?>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required autofocus>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block login-btn">
                        Sign In <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>
</body>
</html>
