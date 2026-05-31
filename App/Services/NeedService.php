<?php
namespace App\Services;
use App\Models\NeedListModel;


class NeedService 
{
    public function __construct(private NeedListModel $needList){}

    public function create(string $category, string $item, string $type, int $user_id): void 
    {
        $created = $this->needList->create($category, $item, $type, $user_id);
        if (!$created)
            throw new \RuntimeException('Something went wrong. Please try again.');
    }

    public function delete(int $id, int $user_id): void
    {
        $result = $this->needList->delete($id, $user_id);

        match ($result) {
            1  => null, // deleted — do nothing
            0  => throw new \RuntimeException('Item not found or already deleted.'),
            -1 => throw new \RuntimeException('Something went wrong. Please try again.'),
        };
    }
}
