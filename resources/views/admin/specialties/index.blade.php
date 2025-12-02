<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Especialidades
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.specialties.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
                    + Nueva especialidad
                </a>
                <a href="{{ route('admin.specialties.bulk') }}" class="px-4 py-2 bg-gray-600 text-white rounded">
                    Carga masiva
                </a>
            </div>



            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Slug</th>
                            <th class="px-4 py-2 text-left">Activa</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($specialties as $specialty)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $specialty->id }}</td>
                                <td class="px-4 py-2">{{ $specialty->name }}</td>
                                <td class="px-4 py-2">{{ $specialty->slug }}</td>
                                <td class="px-4 py-2">
                                    {{ $specialty->active ? 'Sí' : 'No' }}
                                </td>
                                <td class="px-4 py-2 space-x-2">
                                    <a href="{{ route('admin.specialties.edit', $specialty) }}" class="text-blue-600">
                                        Editar
                                    </a>

                                    <form action="{{ route('admin.specialties.destroy', $specialty) }}" method="POST"
                                        style="display:inline" onsubmit="return confirm('¿Eliminar esta especialidad?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center">
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
</x-app-layout>