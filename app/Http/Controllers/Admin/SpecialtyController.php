<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecialtyController extends Controller
{
    public function index()
    {
        $specialties = Specialty::orderBy('name')->paginate(20);

        return view('admin.specialties.index', compact('specialties'));
    }

    public function create()
    {
        return view('admin.specialties.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'slug'   => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        // si no mandás slug, podés generarlo acá
        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

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
            'name'   => ['required', 'string', 'max:255'],
            'slug'   => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Str::slug($data['name']);
        }

        $specialty->update($data);

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Specialty $specialty)
    {
        $specialty->delete();

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', 'Especialidad eliminada.');
    }

    public function bulkForm()
    {
        return view('admin.specialties.bulk');
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'string'],
        ]);

        $lines = preg_split('/\r\n|\r|\n/', $data['items']);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, fn ($l) => $l !== '');

        $created = 0;
        $skipped = 0;

        foreach ($lines as $name) {
            // Evitar duplicados por nombre (case insensitive)
            $exists = Specialty::whereRaw('LOWER(name) = ?', [mb_strtolower($name, 'UTF-8')])->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Specialty::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'active' => true,
            ]);

            $created++;
        }

        return redirect()
            ->route('admin.specialties.index')
            ->with('success', "Carga masiva completada. Creadas: {$created}, omitidas (ya existían): {$skipped}.");
    }
}
