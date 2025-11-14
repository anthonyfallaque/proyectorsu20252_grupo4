<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Color;
use App\Models\Brand;
use App\Models\Vehicletype;
use App\Models\Brandmodel;
use App\Models\Vehicleimage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $vehicles = Vehicle::select(
            'vehicles.id',
            'vi.image as image',
            'vehicles.name',
            'vehicles.code',
            'vehicles.plate',
            'vehicles.year',
            'vehicles.load_capacity',
            'vehicles.fuel_capacity',
            'vehicles.compactation_capacity',
            'vehicles.people_capacity',
            'vehicles.description',
            'vehicles.status',
            'c.name as color_name',
            'b.name as brand_name',
            't.name as type_name',
            'm.name as model_name',
            'vehicles.created_at',
            'vehicles.updated_at'
        )
        ->join('colors as c', 'vehicles.color_id', '=', 'c.id')
        ->join('brands as b', 'vehicles.brand_id', '=', 'b.id')
        ->join('vehicletypes as t', 'vehicles.type_id', '=', 't.id')
        ->leftJoin('vehicleimages as vi', function ($join) {
            $join->on('vehicles.id', '=', 'vi.vehicle_id')
                ->where('vi.profile', '=', 1); // Solo imagen principal
        })
        ->join('brandmodels as m', 'vehicles.model_id', '=', 'm.id')
        ->get();

        if ($request->ajax()) {
            return DataTables::of($vehicles)
                ->addColumn('action', function ($vehicle) {
                   $editBtn = '<button class="btn btn-warning btn-sm btnEditar" id="' . $vehicle->id . '">
                                    <i class="fas fa-edit"></i>
                                </button>';
                    $imagenBtn = '<button class="btn btn-info btn-sm btnImage" id="' . $vehicle->id . '">
                                    <i class="fas fa-image"></i>
                                </button>';
                    
                    
                    $deleteBtn = '<form class="delete d-inline" action="' . route('admin.vehicles.destroy', $vehicle->id) . '" method="POST">
                                    ' . csrf_field() . '
                                    ' . method_field('DELETE') . '
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>';
                    
                    return $editBtn .' '.$imagenBtn. ' ' . $deleteBtn;
                })

                ->rawColumns(['action'])
                ->make(true);
        } else {
            return view('admin.vehicles.index', compact('vehicles'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colors = Color::all()->pluck('name', 'id');
        $brands = Brand::all()->pluck('name', 'id');
        $types = Vehicletype::all()->pluck('name', 'id');
        $models = Brandmodel::all()->pluck('name', 'id');

        return view('admin.vehicles.create', compact('colors', 'brands', 'types', 'models'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100',
            'plate' => 'required|string|max:20',
            'year' => 'required|integer',
            'load_capacity' => 'required|numeric',
            'fuel_capacity' => 'required|numeric',
            'compactation_capacity' => 'required|numeric',
            'people_capacity' => 'required|integer',
            'color_id' => 'required|exists:colors,id',
            'brand_id' => 'required|exists:brands,id',
            'type_id' => 'required|exists:vehicletypes,id',
            'model_id' => 'required|exists:brandmodels,id',
            // description es opcional
        ]);

        try {
            Vehicle::create([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => $request->plate,
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'fuel_capacity' => $request->fuel_capacity,
                'compactation_capacity' => $request->compactation_capacity,
                'people_capacity' => $request->people_capacity,
                'description' => $request->description,
                'status' => $request->status ?? 1,
                'color_id' => $request->color_id,
                'brand_id' => $request->brand_id,
                'type_id' => $request->type_id,
                'model_id' => $request->model_id,
            ]);
            return response()->json(['success' => true, 'message' => 'Vehículo creado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al crear el vehículo: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $colors = Color::all()->pluck('name', 'id');
        $brands = Brand::all()->pluck('name', 'id');
        $types = Vehicletype::all()->pluck('name', 'id');
        $models = Brandmodel::where('brand_id', $vehicle->brand_id)->pluck('name', 'id');


        return view('admin.vehicles.edit', compact('vehicle', 'colors', 'brands', 'types', 'models'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100',
            'plate' => 'required|string|max:20',
            'year' => 'required|integer',
            'load_capacity' => 'required|numeric',
            'fuel_capacity' => 'required|numeric',
            'compactation_capacity' => 'required|numeric',
            'people_capacity' => 'required|integer',
            'color_id' => 'required|exists:colors,id',
            'brand_id' => 'required|exists:brands,id',
            'type_id' => 'required|exists:vehicletypes,id',
            'model_id' => 'required|exists:brandmodels,id',
        ]);

        try {
            $vehicle = Vehicle::findOrFail($id);
            $vehicle->update([
                'name' => $request->name,
                'code' => $request->code,
                'plate' => $request->plate,
                'year' => $request->year,
                'load_capacity' => $request->load_capacity,
                'fuel_capacity' => $request->fuel_capacity,
                'compactation_capacity' => $request->compactation_capacity,
                'people_capacity' => $request->people_capacity,
                'description' => $request->description,
                'status' => $request->status ?? $vehicle->status,
                'color_id' => $request->color_id,
                'brand_id' => $request->brand_id,
                'type_id' => $request->type_id,
                'model_id' => $request->model_id,
            ]);
            return response()->json(['success' => true, 'message' => 'Vehículo actualizado exitosamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el vehículo: ' . $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    try {
        $vehicle = Vehicle::findOrFail($id);

        // Verificamos si tiene relaciones activas
        if ($vehicle->employeegroups()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el vehículo porque está asociado a uno o más grupos de empleados.'
            ]);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehículo eliminado exitosamente.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error al intentar eliminar el vehículo.'
        ]);
    }
}



    public function byType($typeId)
    {
        $vehicles = Vehicle::where('type_id', $typeId)->get();
        return response()->json($vehicles);
    }

    public function getModels($brand_id)
    {
        $models = Brand::where('brand_id', $brand_id)->get(['id', 'name']);
        return response()->json($models);
    }

    public function getImages($vehicle_id)
    {
        $imagesVehicle = Vehicleimage::where('vehicle_id', $vehicle_id)->get();
        $vehicle = Vehicle::findOrFail($vehicle_id);
        return view('admin.vehicles.viewImage', compact('imagesVehicle', 'vehicle'));
    }


    public function storeImages(Request $request)
    {
        try {
            
            $logo ="";
        
            
            if($request->image != null){
                $image  = $request->file('image')->store('public/brand_logo');
                $image = Storage::url($image);

                $profile = 0;
                
                if($request->profile > 0) {
                    Vehicleimage::where('vehicle_id', $request->vehicle_id)->update(['profile' => 0]);
                }

                $vehicle = Vehicleimage::create([
                    'profile' => $request->profile ?? $profile,
                    'vehicle_id' => $request->vehicle_id,
                    'image' => $image
                ]);

            }
           
            //$brand->update($request->all());
            return back()->with('success', 'Imagen Registrada correctamente.');
        } catch (\Throwable $th) {
            return back()->with('success', 'Hubo un error.');
        }
    }

    public function setProfile($image_id)
    {
        $image = Vehicleimage::findOrFail($image_id);
        Vehicleimage::where('vehicle_id', $image->vehicle_id)->update(['profile' => 0]);
        $image->update(['profile' => 1]);

        return back()->with('success', 'Imagen establecida como principal.');
    }

    public function deleteImage($image_id)
    {
        $image = Vehicleimage::findOrFail($image_id);
        
        // Eliminar archivo físico
        Storage::delete($image->image);

        // Eliminar de BD
        $image->delete();

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

}
