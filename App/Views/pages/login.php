<?php
use App\Security\CsrfGuard;
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="auth-container">
    <form method="POST" action="/login">
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Login to continue managing your guild system</p>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="csrf_token" value="<?= e(\App\Security\CsrfGuard::get()) ?>">

        <?php if (!empty($errors['generic'])): ?>
            <div class="message">
                <p class="error">
                    <?= e($errors['generic']) ?>
                </p>
            </div>
        <?php endif; ?>

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            required
        >
        <div class="message">
            <?php if (!empty($errors['email'])): ?>
                <p class="error">
                    <?= e($errors['email']) ?>
                </p>
            <?php endif; ?>
        </div>

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <div class="message">
            <?php if (!empty($errors['password'])): ?>
                <p class="error">
                    <?= e($errors['password']) ?>
                </p>
            <?php endif; ?>
        </div>

        <button type="submit">
            Login
        </button>

        <div class="auth-switch">
            <p>Don't have an account?</p>
            <a href="/signup" class="auth-link">
                Create Account
            </a>
        </div>
    </form>
</div>
</body>
</html>