@extends('layouts.app')

@section('title', 'Nueva especialidad')

@section('content')
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            <h2 class="text-xl font-semibold text-silver mb-4">
                Nueva especialidad
            </h2>

            @if($errors->any())
                <div class="mb-4 text-sm text-red-300">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6">
                <form action="{{ route('admin.specialties.store') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="space-y-4">

                    @csrf

                    {{-- Nombre --}}
                    <div>
                        <label class="block mb-1 text-sm font-medium text-silver/90">
                            Nombre <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="w-full bg-blueDeep/70 border border-blueMid text-silver text-sm rounded-xl px-3 py-2
                                   focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold"
                        >
                    </div>

                    {{-- slug oculto --}}
                    <input type="hidden" name="slug" value="{{ old('slug') }}">

                    {{-- Activa + Destacada --}}
                    <div class="space-y-2">

                        {{-- Activa --}}
                        <label class="inline-flex items-center gap-2 text-sm text-silver/80">
                            <input type="hidden" name="active" value="0">
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                class="rounded border-blueMid bg-blueDeep/80 text-gold focus:ring-gold"
                                {{ old('active', 1) ? 'checked' : '' }}
                            >
                            <span>Activa</span>
                        </label>

                        {{-- Destacada --}}
                        <label class="inline-flex items-center gap-2 text-sm text-silver/80">
                            <input type="hidden" name="is_featured" value="0">
                            <input
                                type="checkbox"
                                name="is_featured"
                                value="1"
                                class="rounded border-blueMid bg-blueDeep/80 text-gold focus:ring-gold"
                                {{ old('is_featured', 0) ? 'checked' : '' }}
                            >
                            <span>Destacada (aparece en “Prácticas más buscadas”)</span>
                        </label>

                        {{-- Imagen --}}
                        <div>
                            <label class="block mb-1 text-sm font-medium text-silver/90">
                                Imagen para tarjeta destacada
                            </label>
                            <input
                                type="file"
                                name="featured_image"
                                accept="image/*"
                                class="w-full text-sm text-silver file:mr-3 file:rounded-lg file:border-0
                                       file:bg-gold/10 file:px-3 file:py-2 file:text-xs file:font-medium
                                       file:text-gold hover:file:bg-gold/20"
                            >
                            <p class="text-[11px] text-silver/60 mt-1">
                                Opcional. Se usa como fondo en las tarjetas de “Prácticas más buscadas”.
                            </p>
                        </div>

                    </div>

                    {{-- Botones --}}
                    <div class="flex gap-3 mt-2">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gold text-blueDeep text-sm font-semibold
                                   hover:bg-goldStrong transition"
                        >
                            Guardar
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
