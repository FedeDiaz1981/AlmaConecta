@extends('layouts.app')

@section('title', 'Cuentas reportadas')

@section('content')
    {{-- Bootstrap 5 solo para esta vista --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-0">
                <div class="p-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <h2 class="h5 mb-0">Cuentas reportadas</h2>
                        <span class="badge bg-secondary">
                            {{ $reports->total() ?? $reports->count() }} pendientes
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @if($reports->isEmpty())
                        <p class="mb-0">No hay reportes pendientes.</p>
                    @else
                        @foreach($reports as $r)
                            @php
                                $profile = $r->profile;
                                $reporter = $r->user;
                                $owner = $profile?->user;
                            @endphp

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $profile?->display_name ?? 'Perfil eliminado' }}</strong>
                                        @if($profile)
                                            <span class="text-muted"> (ID {{ $profile->id }})</span>
                                            @if($profile->is_suspended)
                                                <span class="badge text-bg-danger ms-2">suspendido</span>
                                            @endif
                                        @else
                                            <span class="badge text-bg-secondary ms-2">sin perfil</span>
                                        @endif
                                    </div>

                                    <div class="d-flex gap-2">
                                        @if($profile)
                                            <a href="{{ route('profiles.show', $profile->slug) }}"
                                               class="btn btn-outline-secondary btn-sm"
                                               target="_blank" rel="noopener">
                                                Ver perfil
                                            </a>
                                        @endif
                                        <span class="text-muted small">{{ $r->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="text-muted small">Reportado por</div>
                                            <div class="fw-semibold">
                                                {{ $reporter?->name ?? 'Usuario' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $reporter?->email ?? '—' }}
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="text-muted small">Profesional (cuenta)</div>
                                            <div class="fw-semibold">
                                                {{ $owner?->name ?? '—' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $owner?->email ?? '—' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="text-muted small">Motivo</div>
                                        <div class="border rounded p-2 bg-light">
                                            {{ $r->reason }}
                                        </div>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <form method="POST" action="{{ route('admin.reports.dismiss', $r) }}">
                                            @csrf
                                            <button class="btn btn-outline-secondary">
                                                Descartar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reports.suspend', $r) }}">
                                            @csrf
                                            <button class="btn btn-warning">
                                                Suspender perfil
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.reports.delete_profile', $r) }}"
                                              onsubmit="return confirm('¿Eliminar el perfil profesional? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger">
                                                Borrar perfil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
