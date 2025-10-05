<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;
use Illuminate\Support\Str;

class SpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Reiki', 'Psicología', 'Nutrición', 'Kinesiología', 'Coaching',
            'Yoga', 'Masoterapia', 'Acupuntura', 'Osteopatía', 'Terapia Floral',
            'Fonoaudiología', 'Estética', 'Peluquería'
        ];

        foreach ($items as $name) {
            Specialty::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'active' => true]
            );
        }
    }
}
