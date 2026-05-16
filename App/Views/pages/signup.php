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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<div class="auth-container">
  <form method="post" action="/signup">
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">
      Join your guild management system
    </p>

    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="csrf_token" value="<?= e(\App\Security\CsrfGuard::get()) ?>">

    <label>Name</label>
    <input type="text" name="name" required>

    <div class="message">
      <?php if (!empty($errors['name'])): ?>
        <p class="error"><?= e($errors['name']) ?></p>
      <?php endif; ?>
    </div>

    <label>Email</label>
    <input type="email" name="email" required>
    <div class="message">
      <?php if (!empty($errors['email'])): ?>
        <p class="error"><?= e($errors['email']) ?></p>
      <?php endif; ?>
    </div>

    <label>Password</label>
    <input type="password" name="password" required>
    <div class="message">
      <?php if (!empty($errors['password'])): ?>
        <p class="error"><?= e($errors['password']) ?></p>
      <?php endif; ?>
    </div>

    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" required>
    <div class="message">
      <?php if (!empty($errors['password_conf'])): ?>
        <p class="error"><?= e($errors['password_conf']) ?></p>
      <?php endif; ?>
    </div>

    <button type="submit">Signup</button>

    <div class="auth-switch">
      <p>Already have an account?</p>
      <a href="/login" class="auth-link">Login</a>
    </div>
  </form>
</div>
</body>
</html>
