<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GatewaySdkClient;
use Illuminate\Http\JsonResponse;
use Throwable;

class DeviceController extends Controller
{
    public function status(GatewaySdkClient $gateway): JsonResponse
    {
        try {
            $response = $gateway->deviceStatus();
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'message' => 'Device status check failed.',
                'error' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Device status retrieved successfully.',
            'gateway_response' => $response,
        ]);
    }
}
