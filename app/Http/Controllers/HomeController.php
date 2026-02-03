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

        // Perfiles destacados: top 10 por mejor puntuación promedio (solo con reseñas)
        $featuredProfiles = Profile::query()
            ->with('specialties')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('status', 'approved')
            ->where('is_suspended', false)
            ->whereHas('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('home', [
            'topSpecialties'   => $topSpecialties,
            'featuredProfiles' => $featuredProfiles,
        ]);
    }
}
