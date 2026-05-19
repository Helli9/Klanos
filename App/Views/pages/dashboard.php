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
            if (!empty($playerClass)): ?>
                <div class="data-value"><?= e($playerClass['player_class']) ?></div>
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

<div class="planner-container">
    <div class="planner-header">
        <h3>Upcoming Events</h3>
        <button class="btn-add">+ Schedule</button>
    </div>
    
    <div class="events-grid">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $row): ?>
        <div class="event-card">
            <div class="event-time"><?= e(date('M d, H:i', strtotime($row['event_date']))) ?></div>
            <div class="event-title"><?= e($row['title']) ?></div>
            <div class="event-party">Guild Event</div>
            
            <div class="event-footer">
                <span class="status tentative"><?= e($row['confirmed']) ?>/48 Confirmed</span>
                <div class="event-actions">
                    <?php foreach (['confirmed' => 'Join', 'tentative' => 'Tentative', 'absent' => 'Absent'] as $mode => $label): ?>
                    <form method="POST" action="/home/register_events">
                        <input type="hidden" name="csrf_token" value="<?= e(\App\Security\CsrfGuard::get()) ?>">
                        <input type="hidden" name="event_id"   value="<?= e($row['id']) ?>">
                        <input type="hidden" name="mode"       value="<?= e($mode) ?>">
                        <button type="submit" name="join_event" value="1" class="btn-rsvp"><?= e($label) ?></button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
            <?php endforeach; ?> 
        <?php endif; ?>       
    </div>
</div>