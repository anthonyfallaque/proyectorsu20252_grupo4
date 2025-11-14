<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Brandmodel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class BrandmodelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $models = Brandmodel::select(
            'brandmodels.id',
            'brandmodels.name as model_name',
            'b.name as brand_name',
            'brandmodels.code',
            'brandmodels.description',
            'brandmodels.created_at',
            'brandmodels.updated_at',
        )->join('brands as b','brandmodels.brand_id','=','b.id')->get();
        
        if($request->ajax()){
            return DataTables::of($models)
            ->addColumn('action', function($model){
               $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $model->id . '">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    
                    $deleteBtn = '<form id="delete-form-' . $model->id . '" class="delete d-inline" action="' . route('admin.models.destroy', $model->id) . '" method="POST">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(' . $model->id . ')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                    
                    return $editBtn . ' ' . $deleteBtn;
                })
            ->rawColumns(['action'])
            ->make(true);
        }else{
            return view('admin.models.index', compact('models'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::all()->pluck('name','id');

        return view('admin.models.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            
            Brandmodel::create($request->all());
            return response()->json(['success'=>true,'message' => 'Modelo creado exitosamente'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el modelo: '.$th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = Brandmodel::findOrFail($id);
        return view('admin.models.show', compact('model'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model = Brandmodel::find($id);
        $brands = Brand::all()->pluck('name','id');

        return view('admin.models.edit', compact('model', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $model = Brandmodel::find($id);
            $model->update($request->all());
            return response()->json(['success'=>true,'message' => 'Modelo actualizado exitosamente'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el modelo: '.$th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    try {
        $model = Brandmodel::findOrFail($id);

        // Verifica si hay vehículos asociados
        if ($model->vehicles()->exists()) {
    return response()->json([
        'success' => false,
        'message' => 'No se puede eliminar el modelo porque está asociado a uno o más vehículos.'
    ], 400);
}


        $model->delete();
        return response()->json(['success' => true, 'message' => 'Modelo eliminado exitosamente.'], 200);

    } catch (\Throwable $th) {
        return response()->json(['success' => false, 'message' => 'Error al eliminar el modelo: ' . $th->getMessage()], 500);
    }
}

    public function getModelsByBrand($brand_id)
{
    $models = Brandmodel::where('brand_id', $brand_id)->get();

    return response()->json($models);
}

}
