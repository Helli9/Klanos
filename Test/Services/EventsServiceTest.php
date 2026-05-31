<?php

namespace Tests\Unit\Services;

use App\Models\EventsModel;
use App\Services\EventsService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EventsServiceTest extends TestCase
{
    private $eventsModelMock;
    private EventsService $eventsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventsModelMock = $this->createMock(EventsModel::class);
        $this->eventsService = new EventsService($this->eventsModelMock);
    }

    public function test_register_throws_exception_if_already_registered(): void
    {
        $this->eventsModelMock
            ->expects($this->once())
            ->method('hasUserRegistered')
            ->with(1, 42)
            ->willReturn(true);

        // register() should never be called if already registered
        $this->eventsModelMock->expects($this->never())->method('register');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You are already registered for this event.');

        $this->eventsService->register(1, 42, 'confirmed');
    }

    public function test_register_throws_exception_if_creation_fails(): void
    {
        $this->eventsModelMock
            ->method('hasUserRegistered')
            ->willReturn(false);

        $this->eventsModelMock
            ->expects($this->once())
            ->method('register')
            ->with(1, 42, 'confirmed')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong. Please try again.');

        $this->eventsService->register(1, 42, 'confirmed');
    }

    public function test_register_succeeds_and_returns_void(): void
    {
        $this->eventsModelMock
            ->method('hasUserRegistered')
            ->willReturn(false);

        $this->eventsModelMock
            ->expects($this->once())
            ->method('register')
            ->with(1, 42, 'confirmed')
            ->willReturn(true);

        // Asserts no exception is thrown and method completes execution
        $this->eventsService->register(1, 42, 'confirmed');
    }
}