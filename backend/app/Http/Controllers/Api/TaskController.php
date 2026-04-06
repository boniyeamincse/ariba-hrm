<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->get('tenant_id');
        $user = $request->user();

        $query = Task::where('tenant_id', $tenantId)
            ->with(['assignedTo', 'creator'])
            ->latest();

        // Doctors/Nurses see only their own tasks or group tasks
        if ($user->hasRole('doctor') || $user->hasRole('nurse')) {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to_id', $user->id)
                  ->orWhereNull('assigned_to_id');
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,completed,cancelled',
            'assigned_to_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $validated['created_by_id'] = $request->user()->id;

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,completed,cancelled',
            'assigned_to_id' => 'sometimes|nullable|exists:users,id',
            'due_date' => 'sometimes|nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
