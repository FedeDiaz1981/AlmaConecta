<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar especialidad
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 text-red-700">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.specialties.update', $specialty) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block mb-1">Nombre *</label>
                        <input type="text" name="name"
                               value="{{ old('name', $specialty->name) }}"
                               class="border rounded w-full px-3 py-2" required>
                    </div>

                    {{-- slug oculto; lo seguimos mandando por si ya existe --}}
                    <input type="hidden" name="slug" value="{{ old('slug', $specialty->slug) }}">

                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1"
                                   class="mr-2"
                                   {{ old('active', $specialty->active) ? 'checked' : '' }}>
                            Activa
                        </label>
                    </div>

                    <div class="flex space-x-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded">
                            Actualizar
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
