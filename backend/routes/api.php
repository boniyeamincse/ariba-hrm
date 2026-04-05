<?php

use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'backend-api',
        ]);
    });
});
