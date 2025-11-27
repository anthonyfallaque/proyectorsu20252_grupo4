<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceActivity;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;


use Illuminate\Support\Facades\Log;

class MaintenanceActivityController extends Controller
{
    public function index(Request $request)
{
    $schedule_id = $request->schedule_id;

    $activities = MaintenanceActivity::select('id', 'activity_date', 'observation', 'image', 'completed', 'status', 'created_at')
        ->where('schedule_id', $schedule_id)
        ->orderBy('activity_date', 'asc');

    if ($request->ajax()) {
        return DataTables::of($activities)
            ->addColumn("image_preview", function ($activity) {
                if ($activity->image) {
                    return '<img src="' . asset('storage/' . $activity->image) . '" width="80" height="60" style="object-fit: cover; border-radius: 4px;">';
                }
                return '<span class="badge badge-secondary">Sin imagen</span>';
            })
            ->addColumn("completed_icon", function ($activity) {
                if ($activity->completed == 1 || $activity->completed === true) {
                    return '<i class="fas fa-check-circle" style="font-size: 1.5rem; color: #28a745;"></i>';
                } else {
                    return '<i class="fas fa-times-circle" style="font-size: 1.5rem; color: #dc3545;"></i>';
                }
            })
            ->addColumn("edit", function ($activity) {
                return '<button class="btn btn-secondary btn-sm btnEditActivity" data-id="' . $activity->id . '">
                        <i class="fas fa-edit"></i>
                        </button>';
            })
            ->rawColumns(['image_preview', 'completed_icon', 'edit'])
            ->make(true);
    } else {
        $schedule = MaintenanceSchedule::with(['maintenance', 'vehicle'])->find($schedule_id);
        return view('admin.maintenances.activities.index', compact('activities', 'schedule'));
    }
}

    public function create(Request $request)
    {
        $schedule_id = $request->schedule_id;
        $schedule = MaintenanceSchedule::with(['maintenance', 'vehicle'])->find($schedule_id);

        return view('admin.maintenances.activities.create', compact('schedule'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                "schedule_id" => "required|exists:maintenance_schedules,id",
                "activity_date" => "required|date",
                "observation" => "nullable|string",  // nullable
                "completed" => "nullable|boolean",  // NUEVO
                "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048"
            ]);



            // ... resto del código de validaciones ...

            $data = $request->all();
            $data['completed'] = $request->has('completed') ? true : false;  // NUEVO

            // Manejar la carga de imagen
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('maintenance_activities', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            MaintenanceActivity::create($data);

            return response()->json(["message" => "Actividad actualizada correctamente"], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al actualizar actividad",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $activity = MaintenanceActivity::find($id);
        $schedule = MaintenanceSchedule::with(['maintenance', 'vehicle'])->find($activity->schedule_id);

        return view('admin.maintenances.activities.edit', compact('activity', 'schedule'));
    }

    public function update(Request $request, string $id)
{
    try {
        $activity = MaintenanceActivity::findOrFail($id);

        $request->validate([
            "observation" => "nullable|string",
            "image" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048"
        ]);

        // CAMBIO: Obtener el valor del checkbox correctamente
        $completed = $request->has('completed') ? 1 : 0;

        $data = [
            'observation' => $request->observation,
            'completed' => $completed
        ];

        // Manejar la carga de imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($activity->image) {
                Storage::disk('public')->delete($activity->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('maintenance_activities', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $activity->update($data);

        // DEBUGGING: Verificar que se guardó
        Log::info('Activity updated:', [
            'id' => $id,
            'completed' => $completed,
            'observation' => $request->observation
        ]);

        return response()->json([
            "message" => "Actividad actualizada correctamente",
            "completed" => $completed  // Devolver el valor para verificar
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error updating activity: ' . $e->getMessage());
        return response()->json([
            "message" => "Error al actualizar actividad",
            "error" => $e->getMessage()
        ], 500);
    }
}

    public function destroy(string $id)
{
    try {
        $activity = MaintenanceActivity::findOrFail($id);

        // Validar que no tenga observación ni imagen antes de eliminar
        if ($activity->observation || $activity->image) {
            return response()->json([
                "message" => "No se puede eliminar una actividad que ya tiene información registrada"
            ], 400);
        }

        // Eliminar imagen si existe
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }

        $activity->delete();
        return response()->json(["message" => "Actividad eliminada correctamente"], 200);

    } catch (\Exception $e) {
        return response()->json([
            "message" => "Error al eliminar actividad",
            "error" => $e->getMessage()
        ], 500);
    }
}
}
