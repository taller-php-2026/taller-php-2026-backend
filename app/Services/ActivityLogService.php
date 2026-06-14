<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    public function log(
        string $type,
        string $action,
        string $message,
        ?Request $request = null,
        array $metadata = [],
        ?int $statusCode = null
    ): void {
        try {
            ActivityLog::create([
                'type' => $type,
                'action' => $action,
                'message' => $message,
                'user_id' => $request?->user()?->idUsuario,
                'ip' => $request?->ip(),
                'method' => $request?->method(),
                'url' => $request?->fullUrl(),
                'status_code' => $statusCode,
                'metadata' => $metadata,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('No se pudo guardar el log de actividad en MongoDB.', [
                'error' => $exception->getMessage(),
                'type' => $type,
                'action' => $action,
            ]);
        }
    }
}
