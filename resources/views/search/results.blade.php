<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold">Resultados</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto">

            <div class="mb-4 text-sm text-gray-600">
                @if($q) <strong>Búsqueda:</strong> "{{ $q }}" · @endif
                @if($lat && $lng) <strong>Ubicación:</strong> {{ $loc ?: 'mi ubicación' }} · <strong>Radio:</strong> {{ $r }} km · @endif
                <strong>Remoto:</strong> {{ $remote ? 'Sí' : 'No' }}
            </div>

            @forelse($results as $p)
                <div class="bg-white rounded shadow p-4 mb-3">
                    <div class="flex justify-between">
                        <div>
                            <a class="font-semibold text-lg" href="{{ route('profiles.show', $p->slug) }}">
                                {{ $p->display_name }}
                            </a>
                            <div class="text-sm text-gray-600">
                                {{ $p->service->name ?? 'Sin especialidad' }}
                                @if($p->mode_remote) · Remoto @endif
                                @if($p->mode_presential) · Presencial @endif
                                @if($p->city) · {{ $p->city }}@endif
                                @if($p->state){{ $p->city ? ',' : ' ·' }} {{ $p->state }}@endif
                            </div>
                        </div>
                        <div class="text-right">
                            @if(!is_null($p->distance ?? null))
                                <div class="text-sm text-gray-700">{{ number_format($p->distance, 1) }} km</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded p-6 text-gray-700 shadow">
                    No encontramos resultados. Probá ampliando el radio o incluyendo remoto.
                </div>
            @endforelse

            <div class="mt-6">
                {{ $results->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
