<?php
use App\Models\UserModel;

?>
<div class="dashboard-header">
    <h1>My Dashboard</h1>
    <div class="summary">Static party, recent loot, and current need lists.</div>
</div>

<div class="grid-container">
    <div class="info-card">
        <label>Nickname</label>
        <div class="data-value"><?= e($_SESSION['name'] ?? 'Guest') ?></div>
    </div>

    <div class="info-card">
        <label>Main Class</label>
        <?php
            $current_user = $_SESSION['user_id'];
            $class = UserModel::getClass($current_user);
            if (!empty($class)): ?>
                <div class="data-value"><?= e($class['player_class']) ?></div>
            <?php else: ?>
                <p>No class assigned</p>
            <?php endif; 
        ?>
    </div>

    <div class="info-card">
        <label>Static Party</label>
        <div style="display: flex; gap: 5px; flex-direction: row;">
            <span class="badge">Member 1</span>
            <span class="badge">Member 2</span>
        </div>
    </div>
</div>