<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediStock — Server Error</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? BASE_URL : '') . '/assets/css/style.css?v=7'; ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card" style="text-align: center;">
            <div class="login-header">
                <i class="fas fa-pills"></i>
                <h1>500</h1>
                <p>Server Error</p>
            </div>
            <p style="margin-bottom: 20px;">Something went wrong. Please try again later.</p>
            <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '') . '/dashboard'; ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
