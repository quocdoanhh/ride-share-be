<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Routing\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\AcceptTripRequest;
use App\Http\Requests\LocationTripRequest;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    public function __construct(
        private TripService $tripService
    ) {
    }

    public function store(StoreTripRequest $request)
    {
        try {
            $trip = $this->tripService->createTrip($request->validated());

            return response()->json([
                'data' => $trip,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        $trip = $this->tripService->getTrip($id);

        if (!$trip) {
            return response()->json([
                'message' => 'Cannot find this trip',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => $trip,
        ]);
    }

    public function accept(int $id, AcceptTripRequest $request)
    {
        $trip = $this->tripService->acceptTrip($id, $request->validated());

        return response()->json([
            'data' => $trip,
        ]);
    }

    public function start(int $id)
    {
        $trip = $this->tripService->startTrip($id);

        return response()->json([
            'data' => $trip,
        ]);
    }

    public function end(int $id)
    {
        $trip = $this->tripService->endTrip($id);

        return response()->json([
            'data' => $trip,
        ]);
    }

    public function location(int $id, LocationTripRequest $request)
    {
        $trip = $this->tripService->locationTrip($id, $request->validated());

        return response()->json([
            'data' => $trip,
        ]);
    }
}
