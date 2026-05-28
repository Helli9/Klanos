<?php
use PHPUnit\Framework\TestCase;
use App\Requests\CreateNeedRequest;

class CreateNeedRequestTest extends TestCase
{
    private function makeRequest(array $overrides = []): CreateNeedRequest
    {
        $default = [
            'category' => 'food',
            'item'     => 'rice',
        ];

        return new CreateNeedRequest(array_merge($default, $overrides));
    }

    public function test_valid_input_passes(): void
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

    // category
    public function test_empty_category_fails(): void
    {
        $request = $this->makeRequest(['category' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('category', $request->errors());
    }

    public function test_valid_category_passes(): void
    {
        $request = $this->makeRequest(['category' => 'food']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('category', $request->errors());
    }

    // item
    public function test_empty_item_fails(): void
    {
        $request = $this->makeRequest(['item' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('item', $request->errors());
    }

    public function test_valid_item_passes(): void
    {
        $request = $this->makeRequest(['item' => 'rice']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('item', $request->errors());
    }

    // accessors
    public function test_category_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['category' => 'food']);
        $this->assertSame('food', $request->category());
    }

    public function test_item_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['item' => 'rice']);
        $this->assertSame('rice', $request->item());
    }
}