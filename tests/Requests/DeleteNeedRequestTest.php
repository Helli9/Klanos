<?php
use PHPUnit\Framework\TestCase;
use App\Requests\DeleteNeedRequest;

class DeleteNeedRequestTest extends TestCase
{
    private function makeRequest(array $overrides = []): DeleteNeedRequest
    {
        $default = [
            'need_id' => '5',
        ];

        return new DeleteNeedRequest(array_merge($default, $overrides));
    }

    public function test_valid_input_passes(): void
    {
        $request = $this->makeRequest();
        $this->assertTrue($request->isValid());
    }

    // need_id
    public function test_empty_id_fails(): void
    {
        $request = $this->makeRequest(['need_id' => '']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('need_id', $request->errors());
    }

    public function test_invalid_id_fails(): void
    {
        $request = $this->makeRequest(['need_id' => 'abc']);

        $this->assertFalse($request->isValid());
        $this->assertArrayHasKey('need_id', $request->errors());
    }

    public function test_valid_id_passes(): void
    {
        $request = $this->makeRequest(['need_id' => '5']);

        $this->assertTrue($request->isValid());
        $this->assertArrayNotHasKey('need_id', $request->errors());
    }

    // accessors
    public function test_id_accessor_returns_correct_value(): void
    {
        $request = $this->makeRequest(['need_id' => '5']);
        $this->assertSame(5, $request->id());
    }
}