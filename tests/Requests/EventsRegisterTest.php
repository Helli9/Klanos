<?php
use PHPUnit\Framework\TestCase;
use App\Requests\EventsRegister;

class EventsRegisterTest extends TestCase
{
    private function makeRequest(array $overrides = []): EventsRegister
    {
        $default = [
            'event_id' => '1',
            'mode'     => 'confirmed',
        ];

        return new EventsRegister(array_merge($default, $overrides));
    }

    public function test_valid_input_passes(): void
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

    // event_id
    public function test_empty_event_id_fails(): void
    {
        $request = $this->makeRequest(['event_id' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('event_id', $request->errors());
    }

    public function test_invalid_event_id_fails(): void
    {
        $request = $this->makeRequest(['event_id' => 'abc']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('event_id', $request->errors());
    }

    public function test_valid_event_id_passes(): void
    {
        $request = $this->makeRequest(['event_id' => '5']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('event_id', $request->errors());
    }

    // mode
    public function test_empty_mode_fails(): void
    {
        $request = $this->makeRequest(['mode' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('mode', $request->errors());
    }

    public function test_invalid_mode_fails(): void
    {
        $request = $this->makeRequest(['mode' => 'unknown']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('mode', $request->errors());
    }

    public function test_confirmed_mode_passes(): void
    {
        $request = $this->makeRequest(['mode' => 'confirmed']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('mode', $request->errors());
    }

    public function test_tentative_mode_passes(): void
    {
        $request = $this->makeRequest(['mode' => 'tentative']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('mode', $request->errors());
    }

    public function test_absent_mode_passes(): void
    {
        $request = $this->makeRequest(['mode' => 'absent']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('mode', $request->errors());
    }

    // accessors
    public function test_event_id_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['event_id' => '7']);
        $this->assertSame('7', $request->event_id());
    }
}