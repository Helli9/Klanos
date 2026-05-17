<?php
use App\Models\UserModel;
use App\Models\EventsModel;

$current_user = $_SESSION['user_id'] ?? null;
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
            $class = $current_user ? UserModel::getClass($current_user) : null;
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

<div class="planner-container">
    <div class="planner-header">
        <h3>Upcoming Events</h3>
        <button class="btn-add">+ Schedule</button>
    </div>
    
    <div class="events-grid">
        <?php 
            $current_user = $_SESSION['user_id'];
            $events = EventsModel::getEvents();
            if (!empty($events)):
                foreach ($events as $row): 
                    $Confirmed = EventsModel::getConfirmedCount($row['id']);
        ?>
        <div class="event-card">
            <div class="event-time"><?= e(date('M d, H:i', strtotime($row['event_date']))) ?></div>
            <div class="event-title"><?= e($row['title']) ?></div>
            <div class="event-party">Guild Event</div>
            
            <div class="event-footer">
                <span class="status tentative"><?= e($Confirmed) ?>/48 Confirmed</span>
                <div class="event-actions">
                    <form method="POST" action="/home/register_events"> 
                        <input type="hidden"  name="csrf_token"  value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden"  name="event_id"    value="<?= e($row['id']) ?>">
                        <input type="hidden"  name="mode"        value="confirmed">
                        <button type="submit" name="join_event"  value="1" class="btn-rsvp">Join</button>
                    </form>
                    <form method="POST" action="/home/register_events"> 
                        <input type="hidden"  name="csrf_token"  value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden"  name="event_id"    value="<?= e($row['id']) ?>">
                        <input type="hidden"  name="mode"        value="tentative">
                        <button type="submit" name="join_event"  value="1" class="btn-rsvp">Tentative</button>
                    </form>
                    <form method="POST" action="/home/register_events"> 
                        <input type="hidden"  name="csrf_token"  value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden"  name="event_id"    value="<?= e($row['id']) ?>">
                        <input type="hidden"  name="mode"        value="absent">
                        <button type="submit" name="join_event"  value="1" class="btn-rsvp">Absent</button>
                    </form>
                </div>
            </div>
        </div>
        <?php 
                endforeach; 
            endif;
        ?>
    </div>
</div>