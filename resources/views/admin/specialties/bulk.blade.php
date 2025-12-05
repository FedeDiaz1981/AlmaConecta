@extends('layouts.app')

@section('title', 'Carga masiva de especialidades')

@section('content')
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-4 text-sm text-red-300">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 space-y-4">
                <h2 class="text-lg font-semibold text-silver mb-2">
                    Carga masiva de especialidades
                </h2>

                <p class="text-sm text-silver/80">
                    Pegá una lista de especialidades, <strong>una por línea</strong>.<br>
                    Se crearán como <strong>activas</strong>.<br>
                    Las que ya existan con el mismo nombre se van a <strong>omitir</strong>.
                </p>

                <form action="{{ route('admin.specialties.bulk.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block mb-1 text-sm font-medium text-silver/90">
                            Especialidades
                        </label>
                        <textarea
                            name="items"
                            rows="10"
                            class="w-full bg-blueDeep/70 border border-blueMid text-silver text-sm rounded-xl px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold"
                            placeholder="Psicología Clínica&#10;Terapia Cognitivo Conductual&#10;Coaching Ejecutivo"
                        >{{ old('items') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gold text-blueDeep text-sm font-semibold
                                       hover:bg-goldStrong transition">
                            Procesar
                        </button>

                        <a href="{{ route('admin.specialties.index') }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-blueMid text-sm text-silver/80
                                  hover:bg-blueMid/40 transition">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
