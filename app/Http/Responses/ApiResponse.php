<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function paginated(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $key,
        string $message = 'Success',
        int $code = 200,
    ): JsonResponse {
        return self::success(self::paginatedData($paginator, $resourceClass, $key), $message, $code);
    }

    public static function paginatedData(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $key,
    ): array {
        /** @var JsonResource $resourceClass */
        return [
            $key => $resourceClass::collection($paginator)->resolve(),
            'pagination' => self::pagination($paginator),
        ];
    }

    public static function pagination(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    public static function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
