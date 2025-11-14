<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *  public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
        }
    
        $brands = $query->paginate(10)->appends($request->only('search'));
    
        return view('admin.brands.index', compact('brands'));
    }
     */
    public function index(Request $request)
    {
        $brands = Brand::all();
        if($request->ajax()){
            return DataTables::of($brands)
            ->addColumn('logo', function($brand){
                return "<img src='" . ($brand->logo == '' ? asset('storage/brand_logo/producto_var.webp') : $brand->logo) . "' width='50'>";
            })
            ->addColumn('action', function($brand){
               $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $brand->id . '">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    
                $deleteBtn = '<form id="delete-form-' . $brand->id . '" class="delete d-inline" action="' . route('admin.brands.destroy', $brand->id) . '" method="POST">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(' . $brand->id . ')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>';
                    
                    return $editBtn . ' ' . $deleteBtn;
                })
            ->rawColumns(['logo','action'])
            ->make(true);
        }else{  
            return view('admin.brands.index', compact('brands'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try {

        $logo = "";
        $request->validate([
            'name' => 'unique:brands,name|max:100',
        ], [
            'name.unique' => 'El nombre de la marca ya existe',
            'name.max' => 'El nombre de la marca puede tener máximo 100 caracteres',
        ]);
        

        if($request->logo !=""){
            $image  = $request->file('logo')->store('public/brand_logo'); 
            $logo = Storage::url($image);
        }

        Brand::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logo
        ]);

        //$brand->create($request->all());
        return response()->json(['success'=>true,'message' => 'Marca creada exitosamente'],200);
       } catch (\Throwable $th) {
        return response()->json(['message' => 'Error al crear la marca: '.$th->getMessage()]);
       }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::find($id);
        return view('admin.brands.show', compact('brand'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $brand = Brand::find($id);
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $logo ="";

            $brand = Brand::find($id);
            $request->validate([
                'name'=>'unique:brands,name,'.$id,
               
            ]);
            
            if($request->logo != null){
                $image  = $request->file('logo')->store('public/brand_logo');
                $logo = Storage::url($image);
                
                if($brand->logo != null){
                    Storage::delete($brand->logo); // borrar la imagen anterior
                }

                $brand->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'logo' => $logo
                ]);
                
            }else{
                $brand->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
            }
           
            
            //$brand->update($request->all());
            return response()->json(['success'=>true,'message' => 'Marca actualizada exitosamente'],200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar la marca: '.$th->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
  public function destroy(string $id)
{
    try {
        $brand = Brand::findOrFail($id);

 
        if ($brand->brandmodels()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la marca porque está asociada a uno o más modelos.'
            ], 400);
        }


        if ($brand->vehicles()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la marca porque está asociada a uno o más vehículos.'
            ], 400);
        }

        $brand->delete();
        return response()->json([
            'success' => true,
            'message' => 'Marca eliminada exitosamente.'
        ], 200);

    } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la marca: ' . $th->getMessage()
        ], 500);
    }
}

         
}
