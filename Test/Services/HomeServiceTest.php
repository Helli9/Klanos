<?php

namespace Tests\Unit\Services;

use App\Models\EventsModel;
use App\Models\ItemModel;
use App\Models\NeedListModel;
use App\Models\UserModel;
use App\Services\HomeService;
use PHPUnit\Framework\TestCase;

class HomeServiceTest extends TestCase
{
    private $userModelMock;
    private $needListModelMock;
    private $itemModelMock;
    private $eventsModelMock;
    private HomeService $homeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userModelMock = $this->createMock(UserModel::class);
        $this->needListModelMock = $this->createMock(NeedListModel::class);
        $this->itemModelMock = $this->createMock(ItemModel::class);
        $this->eventsModelMock = $this->createMock(EventsModel::class);

        $this->homeService = new HomeService(
            $this->userModelMock,
            $this->needListModelMock,
            $this->itemModelMock,
            $this->eventsModelMock
        );
    }

    public function test_get_home_data_with_valid_category(): void
    {
        $userId = 123;
        $category = 'Belt';
        $expectedClassArray = ['class' => 'Mage']; // Must be an array to match return type hint

        $this->userModelMock->expects($this->once())
            ->method('getClass')
            ->with($userId)
            ->willReturn($expectedClassArray);

        $this->eventsModelMock->expects($this->once())
            ->method('getEvents')
            ->willReturn(['Event 1', 'Event 2']);

        $this->itemModelMock->expects($this->once())
            ->method('getByCategory')
            ->with('Belt')
            ->willReturn(['Item A', 'Item B']);

        $this->needListModelMock->expects($this->once())
            ->method('getPvp')
            ->with($userId)
            ->willReturn(['PVP 1']);

        $this->needListModelMock->expects($this->once())
            ->method('getPve')
            ->with($userId)
            ->willReturn(['PVE 1']);

        $result = $this->homeService->getHomeData($userId, $category);

        $this->assertSame($expectedClassArray, $result['playerClass']);
        $this->assertSame(['Event 1', 'Event 2'], $result['events']);
        $this->assertSame('Belt', $result['currentCat']);
        $this->assertSame(['Item A', 'Item B'], $result['itemList']);
        $this->assertSame(['PVP 1'], $result['pvpList']);
        $this->assertSame(['PVE 1'], $result['pveList']);
        $this->assertContains('Belt', $result['allowedCategories']);
    }

    public function test_get_home_data_with_invalid_or_null_category(): void
    {
        $userId = 123;
        $category = 'InvalidCategory';
        $expectedClassArray = ['class' => 'Warrior']; // Must be an array to match return type hint

        $this->userModelMock->method('getClass')->willReturn($expectedClassArray);
        $this->eventsModelMock->method('getEvents')->willReturn([]);
        $this->needListModelMock->method('getPvp')->willReturn([]);
        $this->needListModelMock->method('getPve')->willReturn([]);

        // ItemModel should NEVER hit the DB/repo if the category is invalid
        $this->itemModelMock->expects($this->never())->method('getByCategory');

        $result = $this->homeService->getHomeData($userId, $category);

        $this->assertSame($expectedClassArray, $result['playerClass']);
        $this->assertSame('', $result['currentCat']);
        $this->assertSame([], $result['itemList']);
    }
}