<?php

if (!function_exists('apiResponse')) {
    /**
     * Return a standardized API response.
     *
     * @param string $message The message to display.
     * @param int $statusCode The status code to display.
     * @param mixed $data The data object to display.
     * @return \Illuminate\Http\JsonResponse
     */
    function apiResponse($message, $statusCode = 200, $data = null)
    {
        return response()->json([
            'message' => $message,
            'status_code' => $statusCode,
            'data' => $data,
        ], $statusCode);
    }
}
