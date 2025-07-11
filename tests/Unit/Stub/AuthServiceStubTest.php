<?php

namespace Tests\Unit\Stub;

use Tests\TestCase;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class AuthServiceStubTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;
    private $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->authService = new AuthService($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_successfully_with_existing_user()
    {
        $phone = '0123456789';
        $loginData = ['phone' => $phone];

        $existingUser = new User([
            'id' => 1,
            'name' => 'Test User',
            'phone' => $phone,
            'email' => 'test@example.com'
        ]);

        $this->userRepositoryMock
            ->shouldReceive('firstOrCreateByPhone')
            ->once()
            ->with($phone)
            ->andReturn($existingUser);

        $result = $this->authService->login($loginData);

        $this->assertEquals($existingUser, $result);
        $this->assertEquals($phone, $result->phone);
    }

    public function test_login_successfully_with_new_user()
    {
        $phone = '0987654321';
        $loginData = ['phone' => $phone];

        $newUser = new User([
            'id' => 2,
            'name' => null,
            'phone' => $phone,
            'email' => null
        ]);

        $this->userRepositoryMock
            ->shouldReceive('firstOrCreateByPhone')
            ->once()
            ->with($phone)
            ->andReturn($newUser);

        $result = $this->authService->login($loginData);

        $this->assertEquals($newUser, $result);
        $this->assertEquals($phone, $result->phone);
    }

    public function test_login_returns_null_when_repository_fails()
    {
        $phone = '0123456789';
        $loginData = ['phone' => $phone];

        $this->userRepositoryMock
            ->shouldReceive('firstOrCreateByPhone')
            ->once()
            ->with($phone)
            ->andReturn(null);

        $result = $this->authService->login($loginData);

        $this->assertNull($result);
    }

    public function test_verify_login_successfully()
    {
        $phone = '0123456789';
        $code = '123456';
        $verifyData = [
            'phone' => $phone,
            'code' => $code
        ];

        $user = new User([
            'id' => 1,
            'name' => 'Test User',
            'phone' => $phone,
            'login_verification_code' => $code,
            'email' => 'test@example.com'
        ]);

        $this->userRepositoryMock
            ->shouldReceive('whereFirst')
            ->once()
            ->with([
                'phone' => $phone,
                'login_verification_code' => $code
            ])
            ->andReturn($user);

        $result = $this->authService->verifyLogin($verifyData);

        $this->assertEquals($user, $result);
        $this->assertEquals($phone, $result->phone);
        $this->assertEquals($code, $result->login_verification_code);
    }

    public function test_verify_login_returns_null_when_invalid_code()
    {
        $phone = '0123456789';
        $code = '999999';
        $verifyData = [
            'phone' => $phone,
            'code' => $code
        ];

        $this->userRepositoryMock
            ->shouldReceive('whereFirst')
            ->once()
            ->with([
                'phone' => $phone,
                'login_verification_code' => $code
            ])
            ->andReturn(null);

        $result = $this->authService->verifyLogin($verifyData);

        $this->assertNull($result);
    }

    public function test_verify_login_returns_null_when_user_not_found()
    {
        $phone = '9999999999';
        $code = '123456';
        $verifyData = [
            'phone' => $phone,
            'code' => $code
        ];

        $this->userRepositoryMock
            ->shouldReceive('whereFirst')
            ->once()
            ->with([
                'phone' => $phone,
                'login_verification_code' => $code
            ])
            ->andReturn(null);

        $result = $this->authService->verifyLogin($verifyData);

        $this->assertNull($result);
    }

    public function test_login_with_empty_phone_throws_exception()
    {
        $loginData = ['phone' => ''];

        $this->userRepositoryMock
            ->shouldReceive('firstOrCreateByPhone')
            ->once()
            ->with('')
            ->andThrow(new \InvalidArgumentException('Phone number cannot be empty'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Phone number cannot be empty');

        $this->authService->login($loginData);
    }
}
