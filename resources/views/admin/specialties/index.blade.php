@extends('layouts.app')

@section('title', 'Especialidades')

@section('content')
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 text-sm rounded-xl border border-emerald-500/60 bg-emerald-900/40 text-emerald-100 px-4 py-2">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.specialties.create') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong transition">
                    + Nueva especialidad
                </a>
                <a href="{{ route('admin.specialties.bulk') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-blueMid text-sm text-silver/90 hover:bg-blueMid/40 transition">
                    Carga masiva
                </a>

                <form method="GET" action="{{ route('admin.specialties.index') }}"
                      class="flex items-center gap-2 ml-auto w-full sm:w-auto">
                    <input type="text"
                           name="q"
                           value="{{ $q ?? '' }}"
                           placeholder="Buscar especialidad..."
                           class="w-full sm:w-64 rounded-xl border border-blueMid bg-blueDeep/60 px-3 py-2 text-sm text-silver placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gold focus:border-gold">
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blueMid/60 text-silver text-sm font-semibold hover:bg-blueMid/80 transition">
                        Buscar
                    </button>
                    @if(!empty($q))
                        <a href="{{ route('admin.specialties.index') }}"
                           class="text-xs text-silver/70 hover:text-silver">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-blueDeep/80 text-silver/80">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">ID</th>
                            <th class="px-4 py-2 text-left font-semibold">Nombre</th>
                            <th class="px-4 py-2 text-left font-semibold">Slug</th>
                            <th class="px-4 py-2 text-left font-semibold">Activa</th>
                            {{-- NUEVO: columna destacada --}}
                            <th class="px-4 py-2 text-left font-semibold">Destacada</th>
                            <th class="px-4 py-2 text-left font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blueMid/50 bg-blueDeep/60">
                        @forelse($specialties as $specialty)
                            <tr>
                                <td class="px-4 py-2 text-silver/90">
                                    {{ $specialty->id }}
                                </td>
                                <td class="px-4 py-2 text-silver">
                                    {{ $specialty->name }}
                                </td>
                                <td class="px-4 py-2 text-silver/70">
                                    {{ $specialty->slug }}
                                </td>
                                <td class="px-4 py-2">
                                    @if($specialty->active)
                                        <span class="inline-flex px-2 py-1 rounded-full bg-emerald-500/90 text-xs font-semibold text-blueDeep">
                                            Sí
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full bg-slate-600 text-xs font-semibold text-silver">
                                            No
                                        </span>
                                    @endif
                                </td>

                                {{-- NUEVO: indicador de destacada --}}
                                <td class="px-4 py-2">
                                    @if($specialty->is_featured)
                                        <span class="inline-flex px-2 py-1 rounded-full bg-emerald-500/90 text-xs font-semibold text-blueDeep">
                                            Sí
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full bg-slate-600 text-xs font-semibold text-silver">
                                            No
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2">
                                    <div class="flex flex-wrap gap-3 items-center">
                                        <a href="{{ route('admin.specialties.edit', $specialty) }}"
                                           class="text-xs font-semibold text-gold hover:text-goldLight">
                                            Editar
                                        </a>

                                        <form action="{{ route('admin.specialties.destroy', $specialty) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Eliminar esta especialidad?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs font-semibold text-red-400 hover:text-red-300">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-silver/70">
                                    No hay especialidades aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $specialties->links() }}
            </div>
        </div>
    </div>
@endsection
