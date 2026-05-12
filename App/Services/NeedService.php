<?php
namespace App\Services;

use App\Models\NeedListModel;


class NeedService {

    public function create(string $category, string $item, string $type,string $user_id): array {

        $created = NeedListModel::create($category, $item, $type, $user_id);

        return $created
            ? ['success' => true]
            : ['error' => 'Something went wrong. Please try again.'];
    }

    public function delete(string $category, string $item, string $user_id): array {

        $deleted = NeedListModel::delete($category, $item, $user_id);

        return $deleted
            ? ['success' => true]
            : ['error' => 'Something went wrong. Please try again.'];
    }
}
