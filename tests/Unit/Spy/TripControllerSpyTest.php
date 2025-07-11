<?php

namespace Tests\Unit\Spy;

use Tests\TestCase;
use App\Http\Controllers\TripController;
use App\Services\TripService;
use App\Models\Trip;
use App\Models\User;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\AcceptTripRequest;
use App\Http\Requests\LocationTripRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

class TripControllerSpyTest extends TestCase
{
    use RefreshDatabase;

    private TripController $tripController;
    private $tripServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tripServiceMock = Mockery::mock(TripService::class);
        $this->tripController = new TripController($this->tripServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_trip_spy_verify_service_called_with_correct_data()
    {
        $tripData = [
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012],
            'destination_name' => 'Test Destination'
        ];

        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            ...$tripData
        ]);

        $request = Mockery::mock(StoreTripRequest::class);
        $request->shouldReceive('validated')->andReturn($tripData);

        $this->tripServiceMock
            ->shouldReceive('createTrip')
            ->once()
            ->with($tripData)
            ->andReturn($trip);

        $response = $this->tripController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('createTrip')->once();
    }

    public function test_store_trip_spy_verify_exception_handling()
    {
        $tripData = [
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ];

        $request = Mockery::mock(StoreTripRequest::class);
        $request->shouldReceive('validated')->andReturn($tripData);

        $this->tripServiceMock
            ->shouldReceive('createTrip')
            ->once()
            ->with($tripData)
            ->andThrow(new \Exception('Database error'));

        $response = $this->tripController->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Database error', $responseData['message']);

        $this->tripServiceMock->shouldHaveReceived('createTrip')->once();
    }

    public function test_show_trip_spy_verify_service_called_with_correct_id()
    {
        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ]);

        $this->tripServiceMock
            ->shouldReceive('getTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $response = $this->tripController->show(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('getTrip')->once();
    }

    public function test_show_trip_spy_verify_not_found_response()
    {
        $this->tripServiceMock
            ->shouldReceive('getTrip')
            ->once()
            ->with(1)
            ->andReturn(null);

        $response = $this->tripController->show(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Cannot find this trip', $responseData['message']);

        $this->tripServiceMock->shouldHaveReceived('getTrip')->once();
    }

    public function test_accept_trip_spy_verify_service_called_with_correct_parameters()
    {
        $acceptData = [
            'estimated_arrival' => '2024-01-15 10:30:00',
            'fare' => 150000
        ];

        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'driver_id' => 2,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ]);

        $request = Mockery::mock(AcceptTripRequest::class);
        $request->shouldReceive('validated')->andReturn($acceptData);

        $this->tripServiceMock
            ->shouldReceive('acceptTrip')
            ->once()
            ->with(1, $acceptData)
            ->andReturn($trip);

        $response = $this->tripController->accept(1, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('acceptTrip')->once();
    }

    public function test_start_trip_spy_verify_service_called_with_correct_id()
    {
        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'driver_id' => 2,
            'is_started' => true,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ]);

        $this->tripServiceMock
            ->shouldReceive('startTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $response = $this->tripController->start(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('startTrip')->once();
    }

    public function test_end_trip_spy_verify_service_called_with_correct_id()
    {
        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'driver_id' => 2,
            'is_started' => true,
            'is_completed' => true,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ]);

        $this->tripServiceMock
            ->shouldReceive('endTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $response = $this->tripController->end(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('endTrip')->once();
    }

    public function test_location_trip_spy_verify_service_called_with_correct_parameters()
    {
        $locationData = [
            'driver_location' => ['lat' => 10.500, 'lng' => 106.500],
            'current_address' => '123 Test Street'
        ];

        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'driver_id' => 2,
            'is_started' => true,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012],
            'driver_location' => $locationData['driver_location']
        ]);

        $request = Mockery::mock(LocationTripRequest::class);
        $request->shouldReceive('validated')->andReturn($locationData);

        $this->tripServiceMock
            ->shouldReceive('locationTrip')
            ->once()
            ->with(1, $locationData)
            ->andReturn($trip);

        $response = $this->tripController->location(1, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals($trip->toArray(), $responseData['data']);

        $this->tripServiceMock->shouldHaveReceived('locationTrip')->once();
    }

    public function test_all_methods_spy_verify_response_structure()
    {
        $trip = new Trip([
            'id' => 1,
            'user_id' => 1,
            'origin' => ['lat' => 10.123, 'lng' => 106.456],
            'destination' => ['lat' => 10.789, 'lng' => 106.012]
        ]);

        $this->tripServiceMock
            ->shouldReceive('getTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $this->tripServiceMock
            ->shouldReceive('startTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $this->tripServiceMock
            ->shouldReceive('endTrip')
            ->once()
            ->with(1)
            ->andReturn($trip);

        $showResponse = $this->tripController->show(1);
        $this->assertInstanceOf(JsonResponse::class, $showResponse);
        $this->assertEquals(Response::HTTP_OK, $showResponse->getStatusCode());

        $showData = json_decode($showResponse->getContent(), true);
        $this->assertArrayHasKey('data', $showData);
        $this->assertEquals($trip->toArray(), $showData['data']);

        $startResponse = $this->tripController->start(1);
        $this->assertInstanceOf(JsonResponse::class, $startResponse);
        $this->assertEquals(Response::HTTP_OK, $startResponse->getStatusCode());

        $startData = json_decode($startResponse->getContent(), true);
        $this->assertArrayHasKey('data', $startData);
        $this->assertEquals($trip->toArray(), $startData['data']);

        $endResponse = $this->tripController->end(1);
        $this->assertInstanceOf(JsonResponse::class, $endResponse);
        $this->assertEquals(Response::HTTP_OK, $endResponse->getStatusCode());

        $endData = json_decode($endResponse->getContent(), true);
        $this->assertArrayHasKey('data', $endData);
        $this->assertEquals($trip->toArray(), $endData['data']);

        $this->tripServiceMock->shouldHaveReceived('getTrip')->once();
        $this->tripServiceMock->shouldHaveReceived('startTrip')->once();
        $this->tripServiceMock->shouldHaveReceived('endTrip')->once();
    }
}
