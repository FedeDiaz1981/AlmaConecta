<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Resultados') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @if($results->isEmpty())
                    <p>No encontramos resultados. Probá ampliando el radio o incluyendo remoto.</p>
                @else
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($results as $p)
                            <a href="{{ route('profiles.show', $p->slug) }}" class="block border rounded p-4 hover:bg-gray-50">
                                <div class="font-semibold">{{ $p->display_name }}</div>
                                @if($p->specialty)
                                    <div class="text-xs text-gray-700 mt-1">Especialidad: <strong>{{ $p->specialty->name }}</strong>
                                    </div>
                                @endif
                                <div class="text-sm text-gray-600">{{ $p->city }}, {{ $p->state }}</div>
                                <div class="mt-1 space-x-1">
                                    @if($p->mode_presential)
                                        <span class="text-xs bg-blue-100 px-2 py-1 rounded">Presencial</span>
                                    @endif
                                    @if($p->mode_remote)
                                        <span class="text-xs bg-green-100 px-2 py-1 rounded">Remoto</span>
                                    @endif
                                </div>
                                <div class="text-sm mt-2">{{ \Illuminate\Support\Str::limit($p->about, 120) }}</div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>