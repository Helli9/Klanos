<?php
use PHPUnit\Framework\TestCase;
use App\Models\EventsModel;
use Config\Database;

class EventsModelTest extends TestCase 
{
    private $pdo;
    private $eventsModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance();
        
        // Setup isolated test database environment
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS my_project_test");
        $this->pdo->exec("USE my_project_test");
        
        // Bypass foreign key constraints entirely for speed and simplicity
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Recreate the mock environment tables
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS event_attendees (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending'
        )");

        $this->eventsModel = new EventsModel(); 
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS event_attendees");
        $this->pdo->exec("DROP TABLE IF EXISTS events");
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        parent::tearDown();
    }

    // --- TEST: register ---
    
    public function test_register_successfully_saves_attendee(): void
    {
        $eventId = 10;
        $userId = 5;
        $status = "confirmed";

        $result = $this->eventsModel->register($eventId, $userId, $status);

        $this->assertTrue($result);

        // Verify it exists in database
        $stmt = $this->pdo->query("SELECT status FROM event_attendees WHERE event_id = 10 AND user_id = 5");
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($record);
        $this->assertEquals($status, $record['status']);
    }

    // --- TEST: getEvents ---

    public function test_get_events_returns_all_events_with_attendee_counts(): void
    {
        // Seed mock events
        $this->pdo->exec("INSERT INTO events (id, title) VALUES (1, 'Gaming Expo'), (2, 'Dev Meetup')");

        // Seed mock registrations (2 for event 1, 0 for event 2)
        $this->eventsModel->register(1, 100, 'confirmed');
        $this->eventsModel->register(1, 101, 'pending');

        $events = $this->eventsModel->getEvents();

        $this->assertCount(2, $events);

        // Find and assert on Event 1
        $event1 = array_values(array_filter($events, fn($e) => $e['id'] == 1))[0];
        $this->assertEquals('Gaming Expo', $event1['title']);
        $this->assertEquals(2, (int)$event1['confirmed'], "Should count both attendees linked to event 1");

        // Find and assert on Event 2
        $event2 = array_values(array_filter($events, fn($e) => $e['id'] == 2))[0];
        $this->assertEquals('Dev Meetup', $event2['title']);
        $this->assertEquals(0, (int)$event2['confirmed'], "Should return 0 when there are no attendees");
    }

    // --- TEST: getConfirmedCount ---

    public function test_get_confirmed_count_only_counts_confirmed_status(): void
    {
        $eventId = 1;

        // Seed 2 confirmed and 1 pending attendee
        $this->eventsModel->register($eventId, 10, 'confirmed');
        $this->eventsModel->register($eventId, 11, 'confirmed');
        $this->eventsModel->register($eventId, 12, 'pending');
        
        // Seed 1 confirmed attendee for a DIFFERENT event (should be ignored)
        $this->eventsModel->register(2, 13, 'confirmed');

        $count = $this->eventsModel->getConfirmedCount($eventId);

        $this->assertEquals(2, $count, "Should strictly count status='confirmed' records for event 1");
    }

    // --- TEST: hasUserRegistered ---

    public function test_has_user_registered_identifies_existing_registrations(): void
    {
        $eventId = 1;
        $registeredUser = 42;
        $unregisteredUser = 99;

        $this->eventsModel->register($eventId, $registeredUser, 'pending');

        $this->assertTrue($this->eventsModel->hasUserRegistered($eventId, $registeredUser));
        $this->assertFalse($this->eventsModel->hasUserRegistered($eventId, $unregisteredUser));
    }

    // --- TEST: updateStatus ---

    public function test_update_status_modifies_existing_record_correctly(): void
    {
        $eventId = 1;
        $userId = 7;
        
        $this->eventsModel->register($eventId, $userId, 'pending');

        // Execute the update
        $result = $this->eventsModel->updateStatus($eventId, $userId, 'confirmed');

        $this->assertTrue($result);

        // Verify change in DB
        $stmt = $this->pdo->prepare("SELECT status FROM event_attendees WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        
        $this->assertEquals('confirmed', $stmt->fetchColumn());
    }
}