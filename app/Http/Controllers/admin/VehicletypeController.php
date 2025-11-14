<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicletype;
use Yajra\DataTables\Facades\DataTables;

class VehicletypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehiclestypes = Vehicletype::select(
            'id',
            'name', 
            'description',
            'created_at',
            'updated_at'
        )->get();
        
        if($request->ajax()){
            return DataTables::of($vehiclestypes)
            ->addColumn('action', function($vehiclestype){
                return "
                <button class='btn btn-warning btnEditar btn-sm' id='".$vehiclestype->id."'><i class='fas fa-edit'></i></button>
                <form action=". route('admin.vehiclestypes.destroy', $vehiclestype->id) ." id='delete-form-".$vehiclestype->id."' method='POST' class='d-inline'>
                    " . csrf_field() . "
                    " . method_field('DELETE') . "
                    <button type='button' onclick='confirmDelete(".$vehiclestype->id.")' class='btn btn-sm btn-danger'><i class='fas fa-trash'></i></button>
                </form>
                ";
            })
            ->rawColumns(['action'])
            ->make(true);
        }else{
            return view('admin.vehiclestypes.index', compact('vehiclestypes'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vehiclestypes.create');
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
            
            Vehicletype::create($request->all());
            return response()->json(['success'=>true,'message' => 'Tipo de vehiculo creado exitosamente'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el tipo de vehiculo: '.$th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vehiclestype = Vehicletype::findOrFail($id);
        return view('admin.vehicletypes.show', compact('vehiclestype'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehiclestype = Vehicletype::find($id);
        return view('admin.vehiclestypes.edit', compact('vehiclestype'));
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
            
            $vehiclestype = Vehicletype::find($id);
            $vehiclestype->update($request->all());
            return response()->json(['success'=>true,'message' => 'Tipo de vehiculo actualizado exitosamente'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el tipo de vehiculo: '.$th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */


    public function destroy(string $id)
{
    try {
        $vehiclestype = Vehicletype::find($id);

        // Verifica si hay vehículos asociados
        if ($vehiclestype->vehicles()->exists()) {
        return response()->json([
        'success' => false,
        'message' => 'No se puede eliminar el tipo vehiculo porque está asociado a uno o más vehículos.'
    ], 400);
}


        $vehiclestype->delete();
        return response()->json(['success' => true, 'message' => 'Tipo vehiculo eliminado exitosamente.'], 200);

    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => 'Error al eliminar el tipo vehiculo: ' . $th->getMessage()], 500);
    }
}
}