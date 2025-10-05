<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Reiki','Psicología','Nutrición','Osteopatía','Kinesiología','Coaching','Yoga','Masoterapia',
            'Acupuntura','Terapia Floral','Fonoaudiología','Dentista','Peluquería','Estética'
        ];
        foreach ($names as $n) {
            Service::firstOrCreate(['slug'=>Str::slug($n)], ['name'=>$n]);
        }
    }
}
