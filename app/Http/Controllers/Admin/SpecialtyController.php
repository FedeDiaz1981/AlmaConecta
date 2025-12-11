<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SpecialtyController extends Controller
{
    public function index()
    {
        $specialties = Specialty::query()
            ->orderBy('name')
            ->paginate(50);

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('admin.specialties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|max:255',
            'active'         => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',   // ✅ mismo nombre que en el modelo/vistas
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // Normalizamos booleanos
        $data['active']      = $request->boolean('active');
        $data['is_featured'] = $request->boolean('is_featured');

        // Slug
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Imagen destacada (opcional)
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')
                ->store('specialty_backgrounds', 'public');

            $data['featured_image_path'] = $path;
        }

        // No queremos guardar el campo "featured_image" como tal en la BD
        unset($data['featured_image']);

        Specialty::create($data);

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', 'Especialidad creada correctamente.');
    }

    public function edit(Specialty $specialty)
    {
        return view('admin.specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'slug'           => 'nullable|string|max:255',
            'active'         => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',   // ✅ mismo nombre
            'featured_image' => 'nullable|image|max:2048',
        ]);

        // Booleanos
        $data['active']      = $request->boolean('active');
        $data['is_featured'] = $request->boolean('is_featured'); // ✅ antes leías "featured"

        // Slug
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Imagen nueva (si suben una)
        if ($request->hasFile('featured_image')) {

            // opcional: borrar la anterior
            if (!empty($specialty->featured_image_path)) {
                try {
                    Storage::disk('public')->delete($specialty->featured_image_path);
                } catch (\Throwable $e) {
                    // silencioso
                }
            }

            $path = $request->file('featured_image')
                ->store('specialty_backgrounds', 'public');

            $data['featured_image_path'] = $path;
        }

        unset($data['featured_image']);

        $specialty->update($data);

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Specialty $specialty)
    {
        // opcional: borrar imagen asociada
        if (!empty($specialty->featured_image_path)) {
            try {
                Storage::disk('public')->delete($specialty->featured_image_path);
            } catch (\Throwable $e) {
                // noop
            }
        }

        $specialty->delete();

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', 'Especialidad eliminada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | CARGA MASIVA
    |--------------------------------------------------------------------------
    */

    // GET /admin/specialties/bulk
    public function bulkForm()
    {
        return view('admin.specialties.bulk');
    }

    // POST /admin/specialties/bulk
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|string',
        ]);

        $lines = preg_split('/\r\n|\r|\n/', $data['items']);
        $lines = array_filter(array_map('trim', $lines));

        $creadas = 0;

        foreach ($lines as $name) {
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);

            $exists = Specialty::where('name', $name)
                ->orWhere('slug', $slug)
                ->exists();

            if ($exists) {
                continue;
            }

            Specialty::create([
                'name'         => $name,
                'slug'         => $slug,
                'active'       => true,
                'is_featured'  => false,
            ]);

            $creadas++;
        }

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', "Proceso terminado. Se crearon {$creadas} especialidades nuevas.");
    }
}
