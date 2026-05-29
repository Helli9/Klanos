<?php
use PHPUnit\Framework\TestCase;
use App\Models\NeedListModel;
use Config\Database;

class NeedListModelTest  extends TestCase 
{
    private $pdo;
    private $needListModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance();

        // Prepare sandbox database
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS my_project_test");
        $this->pdo->exec("USE my_project_test");

        // 1. MUST create the dependent users table first to avoid FK errors
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL
        )");

        // 2. Insert dummy user so user_id 2 actually exists
        $this->pdo->exec("INSERT IGNORE INTO users (id, username) VALUES (999, 'otheruser')");
        $this->pdo->exec("INSERT IGNORE INTO users (id, username) VALUES (2, 'testuser')");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS need_list (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            category VARCHAR(50) NOT NULL,
            item VARCHAR(100) NOT NULL,
            type VARCHAR(50),
            status ENUM('pending', 'done') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_need_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
        )");
        $this->needListModel = new NeedListModel(); 
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS need_list");
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        parent::tearDown();
    }

    public function test_create_need_list_successfully(): void
    {
        $category = "Archboss Weapon";
        $item = "Cordy";
        $type = "pvp";
        $user_id = 2;

        $result = $this->needListModel->create($category, $item, $type, $user_id);

        $this->assertTrue($result);

        $stmt = $this->pdo->query("SELECT category, item FROM need_list WHERE user_id = 2");
        $needList = $stmt->fetch();

        $this->assertNotEmpty($needList);
        $this->assertEquals($category, $needList['category']);
    }

    public function test_get_pvp_returns_only_pvp_items_for_user(): void
    {
        $userId = 2;

        // Seed mix of PvP and PvE data for this user
        $this->needListModel->create("Weapon", "Greatsword", "pvp", $userId);
        $this->needListModel->create("Armor", "Helmet", "pve", $userId);
        $this->needListModel->create("Accessory", "Ring", "pvp", $userId);
        
        // Seed data for a completely different user (should not be fetched)
        $this->needListModel->create("Weapon", "Dagger", "pvp", 999); 

        // Execute target method
        $pvpItems = $this->needListModel->getPvp($userId);

        // Assertions
        $this->assertCount(2, $pvpItems, "Should only return 2 PvP items for user 2");
        $this->assertEquals("Greatsword", $pvpItems[0]['item']);
        $this->assertEquals("Ring", $pvpItems[1]['item']);
    }

    public function test_get_pve_returns_only_pve_items_for_user(): void
    {
        $userId = 2;

        // Seed data
        $this->needListModel->create("Weapon", "Bow", "pve", $userId);
        $this->needListModel->create("Armor", "Boots", "pvp", $userId);

        // Execute target method
        $pveItems = $this->needListModel->getPve($userId);

        // Assertions
        $this->assertCount(1, $pveItems);
        $this->assertEquals("Bow", $pveItems[0]['item']);
    }

    public function test_delete_removes_item_successfully_and_returns_one(): void
    {
        $userId = 2;
        $this->needListModel->create("Weapon", "Staff", "pvp", $userId);

        // Fetch the generated ID of the inserted row
        $stmt = $this->pdo->query("SELECT id FROM need_list WHERE item = 'Staff' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $insertedId = (int)$row['id'];

        // Execute delete
        $affectedRows = $this->needListModel->delete($insertedId, $userId);

        // Assertions
        $this->assertEquals(1, $affectedRows, "Expected delete to return 1 affected row");

        // Double check database is actually empty now
        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM need_list WHERE id = ?");
        $checkStmt->execute([$insertedId]);
        $this->assertEquals(0, $checkStmt->fetchColumn());
    }

    public function test_delete_returns_zero_if_item_does_not_exist_or_wrong_user(): void
    {
        $userId = 2;
        $this->needListModel->create("Weapon", "Shield", "pvp", $userId);

        // Fetch the generated ID
        $stmt = $this->pdo->query("SELECT id FROM need_list WHERE item = 'Shield' LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $insertedId = (int)$row['id'];

        // Attempt to delete with the wrong user_id (999 instead of 2)
        $affectedRowsWrongUser = $this->needListModel->delete($insertedId, 999);
        $this->assertEquals(0, $affectedRowsWrongUser, "Should return 0 rows affected because user_id didn't match");

        // Attempt to delete a non-existent item ID
        $affectedRowsNonExistent = $this->needListModel->delete(99999, $userId);
        $this->assertEquals(0, $affectedRowsNonExistent, "Should return 0 rows affected because ID doesn't exist");
    }

}