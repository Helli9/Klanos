<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\NeedListModel;
use App\Models\ItemModel;
use App\Models\EventsModel;

class HomeService
{
    public function __construct(
        private UserModel $users,
        private NeedListModel $needList,
        private ItemModel $item,
        private EventsModel $event,
    ) {}

    public function getHomeData(int $userId, ?string $category): array
    {
        // dashboard
        $playerClass = $this->users->getClass($userId);

        $events = $this->event->getEvents();

        // need list
        $allowedCategories = [
            'Archboss Weapon',
            'Belt',
            'Bracelet'
        ];

        $currentCat = in_array($category, $allowedCategories, true)
            ? $category
            : '';

        $itemList = $currentCat
            ? $this->item->getByCategory($currentCat)
            : [];

        return [
            'playerClass'       => $playerClass,
            'events'            => $events,
            'allowedCategories' => $allowedCategories,
            'currentCat'        => $currentCat,
            'itemList'          => $itemList,
            'pvpList'           => $this->needList->getPvp($userId),
            'pveList'           => $this->needList->getPve($userId),
        ];
    }
}

