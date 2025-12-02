<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Carga masiva de especialidades
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 text-red-700">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <p class="text-sm text-gray-700">
                    Pegá una lista de especialidades, <strong>una por línea</strong>.  
                    Se crearán como <strong>activas</strong>.  
                    Las que ya existan con el mismo nombre se van a <strong>omitir</strong>.
                </p>

                <form action="{{ route('admin.specialties.bulk.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block mb-1 text-sm font-medium">Especialidades</label>
                        <textarea name="items"
                                  rows="10"
                                  class="border rounded w-full px-3 py-2"
                                  placeholder="Psicología Clínica&#10;Terapia Cognitivo Conductual&#10;Coaching Ejecutivo">{{ old('items') }}</textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded">
                            Procesar
                        </button>
                        <a href="{{ route('admin.specialties.index') }}"
                           class="px-4 py-2 bg-gray-300 rounded">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
