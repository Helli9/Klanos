<?php
namespace App\Validators;

class NeedListValidators
{
    public static function validateNeedList(string $category, string $item): array {
        $errors = [];
        // 3. BASIC VALIDATION
        if(empty($category)) $errors['category'] = "Please select a category";  
        if(empty($item)) $errors['item'] = "Please select an item";

        return $errors;
    }

}