<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ResponseBuilderTrait
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data = null, $message = 'success', $statusCode = Response::HTTP_OK)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($errors = null,$message = null,$statusCode = Response::HTTP_BAD_REQUEST)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ];

        return response()->json($response, $statusCode);
    }
}
