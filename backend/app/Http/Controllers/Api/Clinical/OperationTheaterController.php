<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\OperationTheater;
use Illuminate\Http\Request;

class OperationTheaterController extends Controller
{
    public function index()
    {
        $rooms = OperationTheater::query()
            ->latest()
            ->get();

        return response()->json(['data' => $rooms]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:60',
            'status' => 'nullable|string|in:available,occupied,maintenance',
        ]);

        $room = OperationTheater::create($validated);

        return response()->json([
            'message' => 'Operation theater room created successfully.',
            'room' => $room,
        ], 201);
    }
}
