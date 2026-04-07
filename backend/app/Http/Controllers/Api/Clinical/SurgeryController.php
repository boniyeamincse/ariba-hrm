<?php

namespace App\Http\Controllers\Api\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Surgery;
use Illuminate\Http\Request;

class SurgeryController extends Controller
{
    public function index()
    {
        $surgeries = Surgery::query()
            ->latest()
            ->get();

        return response()->json(['data' => $surgeries]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:60',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $surgery = Surgery::create($validated);

        return response()->json([
            'message' => 'Surgery procedure created successfully.',
            'surgery' => $surgery,
        ], 201);
    }
}
