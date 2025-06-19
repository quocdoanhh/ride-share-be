<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\DriverService;
use Illuminate\Routing\Controller;
use App\Http\Requests\StoreDriverRequest;
use Symfony\Component\HttpFoundation\Response;

class DriverController extends Controller
{
    public function __construct(
        private DriverService $driverService,
        private UserService $userService
    ) {
    }

    public function show()
    {
        return response()->json([
            'data' => auth()->user()->load('driver'),
        ]);
    }

    public function store(StoreDriverRequest $request)
    {
        try {
            $this->userService->updateUser($request->only('name'));
            $user = $this->driverService->updateOrCreateDriver($request->except('name'));

            return response()->json([
                'data' => $user,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
