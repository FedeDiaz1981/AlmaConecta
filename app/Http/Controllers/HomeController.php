<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use App\Models\Profile;

class HomeController extends Controller
{
    public function index()
    {
        // Especialidades destacadas (activas + marcadas como destacadas)
        $topSpecialties = Specialty::query()
            ->where('active', true)
            ->where('is_featured', true)   // campo booleano "Destacada"
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Perfiles destacados: top 20 por cantidad de visualizaciones
        $featuredProfiles = Profile::query()
            ->with('specialties')
            ->where('status', 'approved')
            ->orderByDesc('views_count')   // más vistos primero
            ->orderByDesc('id')            // desempate estable
            ->limit(20)
            ->get();

        return view('home', [
            'topSpecialties'   => $topSpecialties,
            'featuredProfiles' => $featuredProfiles,
        ]);
    }
}
