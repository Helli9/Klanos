<?php
use PHPUnit\Framework\TestCase;
use App\Models\ItemModel;
use Config\Database;

class ItemModelTest extends TestCase 
{
    private $pdo;
    private $itemModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance();
        
        // Setup isolated test database environment
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS my_project_test");
        $this->pdo->exec("USE my_project_test");

        // Create mock items schema
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            item VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL
        )");

        $this->itemModel = new ItemModel(); 
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS items");
        parent::tearDown();
    }

    public function test_get_by_category_returns_matching_items(): void
    {
        // Seed test items across multiple categories
        $stmt = $this->pdo->prepare("INSERT INTO items (item, category) VALUES (?, ?)");
        $stmt->execute(["Cordy", "Archboss Weapon"]);
        $stmt->execute(["Ambusher Boots", "Armor"]);
        $stmt->execute(["Toublek", "Archboss Weapon"]);

        // Execute the target method for "Archboss Weapon"
        $results = $this->itemModel->getByCategory("Archboss Weapon");

        // Assertions
        $this->assertCount(2, $results, "Should return exactly 2 items for this category");
        
        // Check that the returned content matches our seeded data structure
        $this->assertEquals("Cordy", $results[0]['item']);
        $this->assertEquals("Toublek", $results[1]['item']);
    }

    public function test_get_by_category_returns_empty_array_when_no_match_found(): void
    {
        // Seed an item in a completely different category
        $stmt = $this->pdo->prepare("INSERT INTO items (item, category) VALUES (?, ?)");
        $stmt->execute(["Golden Ring", "Accessory"]);

        // Request a category that doesn't exist in our seeded dataset
        $results = $this->itemModel->getByCategory("NonExistentCategory");

        // Assertions
        $this->assertIsArray($results);
        $this->assertEmpty($results, "Should safely return an empty array if no items match");
    }
}