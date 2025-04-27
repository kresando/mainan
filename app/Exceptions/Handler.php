<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log critical exceptions separately for better monitoring
            if ($this->isCritical($e)) {
                Log::critical('Critical exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
        
        // Customize how Livewire exceptions are handled
        $this->renderable(function (Throwable $e, $request) {
            if ($request->hasHeader('X-Livewire') && !config('app.debug')) {
                // In production, for Livewire requests, provide a cleaner error message
                if ($e instanceof \Illuminate\Database\QueryException) {
                    return response()->json([
                        'message' => 'A database error occurred. Please try again later.',
                    ], 500);
                }
            }
            
            return null;
        });
    }
    
    /**
     * Determine if the exception is critical.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    protected function isCritical(Throwable $e): bool
    {
        return $e instanceof \Error ||
               $e instanceof \Illuminate\Database\QueryException ||
               $e instanceof \PDOException;
    }
} 