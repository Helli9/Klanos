<?php 
use App\Models\ItemModel; 
use App\Models\NeedListModel; 

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="needList_header">
    <h1>My Need lists</h1>
    <div class="summary">Manage your PvP and PvE need lists using the guild item catalog.</div>
</div>
<div class="form-container">
    <div class="create_need_list">
        <!-- Form 1: Category Selection (GET) -->
        <form method="GET">
            <input type="hidden" name="tab" value="need_lists">
            
            <label for="category">Category</label>
            <select name="category" id="category" onchange="this.form.submit()">
                <option value="">-- Select Category --</option>
                <?php 
                    $currentCat = $_GET['category'] ?? ''; 
                    $categories = ['Archboss Weapon', 'belt', 'Bracelet']; // Expand as needed
                    if (!in_array($currentCat, $categories, strict: true)) {
                        $currentCat = '';
                    }
                    foreach ($categories as $cat): 
                ?>
                    <option value="<?= e($cat) ?>" <?= $currentCat === $cat ? 'selected' : '' ?>>
                        <?= e(ucfirst($cat)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Form 2: Item Submission (POST) -->
        <?php if (!empty($currentCat)): ?>
        <form method="POST" action="/home/create-need">
            <input type="hidden" name="tab" value="need_lists">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="category" value="<?= e($currentCat) ?>">

            <label for="item">Item</label>
            <select name="item" id="item" required>
                <option value="">-- Select Item --</option>
                <?php
                    $itemList = ItemModel::getByCategory($currentCat);
                    foreach ($itemList as $items):
                ?>
                    <option value="<?= e($items['item']) ?>"><?= e($items['item']) ?></option>
                <?php endforeach; ?>
            </select>
                
            <div class="button-group">
                <button type="submit" name="mode" value="pvp" class="btn-pvp">Add to PvP</button>
                <button type="submit" name="mode" value="pve" class="btn-pve">Add to PvE</button>
            </div>

            <!-- Error Handling -->
            <div class="error-messages">
                <?php foreach (['user_id', 'item', 'category', 'type'] as $field): ?>
                    <?php if(!empty($errors[$field])): ?>
                        <p class="errorText"><?= e($errors[$field]) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </form> 
        <?php endif; ?>
    </div>   
</div>



<div class="need_list">
    <label>PvP Need list :</label>
    <?php 
        $current_user = $_SESSION['user_id'];
        $item = NeedListModel::getPvp($current_user);
        if (!empty($item)):
            foreach ($item as $row):
    ?>
        <div class="needed">
            <div>
                <label class="category">Category: </label>
                <?= e($row['category']) ?></div>
            <div>
                <label class="category">Name:</label>
                <?= e($row['item']) ?>
            </div>
            <form method="POST"  action="/home/delete-need">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="category"   value="<?= e($row['category']) ?>">
                <input type="hidden" name="item"       value="<?= e($row['item']) ?>">
                <input type="hidden" name="mode"       value="pvp">
                <button type="submit" name="delete_item" value="1" class="delete_item">Delete</button>
            </form>
        </div>

    <?php 
            endforeach; 
        else:
    ?>
        <p>No items found for this selection.</p>
    <?php endif; ?>
</div>

<div class="need_list">
    <label>PvE Need list :</label>
    <?php 
        $current_user = $_SESSION['user_id'];
        
        $item = NeedListModel::getPve($current_user);
        if (!empty($item)):
            foreach ($item as $row):
    ?>
        <div class="needed">
            <div>
                <label class="category">Category: </label>
                <?= e($row['category']) ?></div>
            <div>
                <label class="category">Name:</label>
                <?= e($row['item']) ?>
            </div>
            <form method="POST" action="/home/delete-need">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="category"   value="<?= e($row['category']) ?>">
                <input type="hidden" name="item"       value="<?= e($row['item']) ?>">
                <input type="hidden" name="need_id"    value="<?= e($row['id']) ?>">
                <input type="hidden" name="mode"       value="pve">
                <button type="submit" name="delete_item" value="1" class="delete_item">Delete</button>
            </form>
        </div>
    <?php 
            endforeach; 
        else:
    ?>
        <p>No items found for this selection.</p>
    <?php endif; ?>
</div>

