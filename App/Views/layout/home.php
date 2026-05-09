<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$tab = $_GET['tab'] ?? 'dashboard';
$view = $tab;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="stylesheet" href="/css/homeStyle.css">
</head>
<body>
<div>
  <nav>
        <div id="welcome">Welcome, <?= e($_SESSION['name'] ?? 'Guest') ?></div>
        <form method="POST" action="/logout">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" id="logout-B">Logout</button>
        </form>
    </nav>

        <header id="secondary-nav">
            <div id="tab">
                <a href="/home?tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="/home?tab=need_lists" class="<?= $tab === 'need_lists' ? 'active' : '' ?>">Needlists</a>
            </div>
            <button id="refresh-B" onclick="window.location.reload();">Refresh</button>
        </header>

        <main>
            <?php include __DIR__ . "/../pages/{$tab}.php"; ?>
        </main>
    </div>

</div>
</body>
</html>