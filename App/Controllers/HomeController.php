<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\EventsModel;
use App\Models\NeedListModel;
use App\Models\ItemModel;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        // ── Dashboard data ────────────────────────────────────────────────
        $playerClass = UserModel::getClass($userId);

        $rawEvents = EventsModel::getEvents();

        $events = array_map(function (array $event) {
            $event['confirmed'] = EventsModel::getConfirmedCount($event['id']);
            return $event;
        }, $rawEvents);

        // ── Need list data ────────────────────────────────────────────────
        $allowedCategories = ['Archboss Weapon', 'Belt', 'Bracelet'];
        $currentCat = $_GET['category'] ?? ''; 
        if (!in_array($currentCat, $allowedCategories, strict: true)) {
            $currentCat = '';
        }

        $itemList  = $currentCat ? ItemModel::getByCategory($currentCat) : [];
        $pvpList   = NeedListModel::getPvp($userId);
        $pveList   = NeedListModel::getPve($userId);


        $this->view('layout/home', [
            'name'              => $_SESSION['name'] ?? 'Guest',
            // dashboard
            'playerClass'       => $playerClass,
            'events'            => $events,
            // need lists
            'allowedCategories' => $allowedCategories,
            'currentCat'        => $currentCat,
            'itemList'          => $itemList,
            'pvpList'           => $pvpList,
            'pveList'           => $pveList,
        ]);
    }
}

