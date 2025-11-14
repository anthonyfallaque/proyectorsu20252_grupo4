<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reason;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Change;

class ReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reasons = Reason::select(
            'id',
            'name',
            'description'

        )->get();

        if ($request->ajax()) {
            return DataTables::of($reasons)
                ->addColumn('action', function ($reason) {
                    return "
                <button class='btn btn-warning btn-sm btnEditar' id='" . $reason->id . "'><i class='fas fa-edit'></i></button>
                <form action=" . route('admin.reasons.destroy', $reason->id) . " id='delete-form-" . $reason->id . "' method='POST' class='d-inline'>
                    " . csrf_field() . "
                    " . method_field('DELETE') . "
                    <button type='button' onclick='confirmDelete(" . $reason->id . ")' class='btn btn-sm btn-danger'><i class='fas fa-trash'></i></button>
                </form>
                ";
                })
                ->rawColumns(['action'])
                ->make(true);
        } else {
            return view('admin.reasons.index', compact('reasons'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.reasons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            Reason::create($request->all());
            return response()->json(['success' => true, 'message' => 'Motivo creado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el motivo: ' . $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reason = Reason::findOrFail($id);
        return view('admin.reasons.show', compact('reason'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $reason = Reason::find($id);
        return view('admin.reasons.edit', compact('reason'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $reason = Reason::find($id);
            $reason->update($request->all());
            return response()->json(['success' => true, 'message' => 'Motivo actualizado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el motivo: ' . $th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $reason = Reason::findOrFail($id);

            // Verifica si hay cambios asociados a este motivo
            $hasChanges = \App\Models\Change::where('reason_id', $reason->id)->exists();

            if ($hasChanges) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el motivo porque está asociado a uno o más cambios.'
                ], 400);
            }

            $reason->delete();
            return response()->json([
                'success' => true,
                'message' => 'Motivo eliminado exitosamente.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el motivo: ' . $th->getMessage()
            ]);
        }
    }
}
