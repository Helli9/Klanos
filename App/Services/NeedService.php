<?php
namespace App\Services;
use App\Models\NeedListModel;


class NeedService 
{
    public function __construct(private NeedListModel $needList){}

    public function create(string $category, string $item, string $type, int $user_id): void 
    {
        //$created = NeedListModel::create($category, $item, $type, $user_id);
        $created = $this->needList->create($category, $item, $type, $user_id);

        if (!$created)
            throw new \RuntimeException('Something went wrong. Please try again.');
    }

    public function delete(int $id, int $user_id): void
    {
        //$deleted = NeedListModel::delete($id, $user_id);
        $deleted = $this->needList->delete($id, $user_id);

        if (!$deleted)
            throw new \RuntimeException('Something went wrong. Please try again.');
    }
}
