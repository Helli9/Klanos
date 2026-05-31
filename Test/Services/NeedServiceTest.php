<?php

namespace Tests\Unit\Services;

use App\Models\NeedListModel;
use App\Services\NeedService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NeedServiceTest extends TestCase
{
    private $needListModelMock;
    private NeedService $needService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->needListModelMock = $this->createMock(NeedListModel::class);
        $this->needService = new NeedService($this->needListModelMock);
    }

    // --- Create Method Tests ---

    public function test_create_succeeds_and_returns_void(): void
    {
        $this->needListModelMock
            ->expects($this->once())
            ->method('create')
            ->with('Belt', 'Golden Belt', 'PVP', 1)
            ->willReturn(true);

        $this->needService->create('Belt', 'Golden Belt', 'PVP', 1);
    }

    public function test_create_throws_exception_on_failure(): void
    {
        $this->needListModelMock
            ->method('create')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong. Please try again.');

        $this->needService->create('Belt', 'Golden Belt', 'PVP', 1);
    }

    // --- Delete Method Tests ---

    public function test_delete_succeeds_and_returns_void(): void
    {
        $this->needListModelMock
            ->expects($this->once())
            ->method('delete')
            ->with(42, 1)
            ->willReturn(true);

        $this->needService->delete(42, 1);
    }

    public function test_delete_throws_exception_on_failure(): void
    {
        $this->needListModelMock
            ->method('delete')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something went wrong. Please try again.');

        $this->needService->delete(42, 1);
    }
}