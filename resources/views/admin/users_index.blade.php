<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cuentas de usuarios') }}
        </h2>
    </x-slot>

    {{-- Bootstrap 5 sólo para esta vista --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="alert alert-success mb-4">{{ session('status') }}</div>
            @endif

            {{-- CUENTAS (pendientes listadas aquí) --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="h5 mb-0">Cuentas pendientes</h3>
                        <span class="badge bg-secondary">{{ $pending->total() ?? $pending->count() }}</span>
                    </div>
                </div>

                <div class="card-body">
                    @if($pending->isEmpty())
                        <div class="alert alert-info mb-0">No hay cuentas pendientes.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Rol</th>
                                        <th scope="col">Estado cuenta</th>
                                        <th scope="col">Estado perfil</th>
                                        <th scope="col" class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($pending as $u)
                                    @php
                                        // Estado de la CUENTA
                                        $accountStatus = strtolower((string)($u->account_status ?? $u->status ?? ''));
                                        $accountClass = match ($accountStatus) {
                                            'active'    => 'text-bg-success',
                                            'suspended' => 'text-bg-secondary',
                                            'pending'   => 'text-bg-warning',
                                            'rejected'  => 'text-bg-danger',
                                            default     => 'text-bg-light',
                                        };

                                        // Perfil y edición pendiente
                                        $profile = \App\Models\Profile::where('user_id', $u->id)->first();

                                        // Estado del PERFIL
                                        $profileStatus = strtolower((string)($profile->status ?? ''));
                                        $profileClass = match ($profileStatus) {
                                            'approved'  => 'text-bg-success',
                                            'pending'   => 'text-bg-warning',
                                            'rejected'  => 'text-bg-danger',
                                            default     => 'text-bg-light',
                                        };

                                        // Última edición pendiente (si existe)
                                        $pendingEdit = $profile
                                            ? \App\Models\Edit::where('profile_id', $profile->id)
                                                ->where('status', 'pending')
                                                ->latest()
                                                ->first()
                                            : null;

                                        // Normalizar payload
                                        $payload = $pendingEdit?->payload;
                                        if (!is_array($payload)) {
                                            $payload = json_decode($payload ?? '[]', true) ?: [];
                                        }

                                        // Helper para tomar del payload o del perfil
                                        $val = function(string $key) use ($payload, $profile) {
                                            return array_key_exists($key, $payload) ? $payload[$key] : ($profile->$key ?? null);
                                        };

                                        $displayName  = $val('display_name');
                                        $serviceId    = $val('service_id');
                                        $serviceName  = $serviceId ? optional(\App\Models\Service::find($serviceId))->name : null;

                                        $nr = (bool)($payload['mode_remote']     ?? $profile->mode_remote     ?? false);
                                        $np = (bool)($payload['mode_presential'] ?? $profile->mode_presential ?? false);
                                        $mode = $nr && $np ? 'Remoto y presencial' : ($nr ? 'Remoto' : ($np ? 'Presencial' : null));

                                        $country      = $val('country');
                                        $state        = $val('state');
                                        $city         = $val('city');
                                        $address      = $val('address');
                                        $whatsapp     = $val('whatsapp');
                                        $contactEmail = $val('contact_email');
                                        $about        = $val('about');
                                        $photoPath    = $val('photo_path');
                                        $videoUrl     = $val('video_url');
                                    @endphp

                                    <tr>
                                        <td>{{ $u->id }}</td>
                                        <td class="fw-semibold">{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td><span class="badge bg-outline border">{{ $u->role }}</span></td>
                                        <td>
                                            <span class="badge {{ $accountClass }}">
                                                {{ $accountStatus !== '' ? $accountStatus : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $profileClass }}">
                                                {{ $profileStatus !== '' ? $profileStatus : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            {{-- Ver solicitud --}}
                                            <button
                                                class="btn btn-outline-secondary btn-sm me-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#user-request-{{ $u->id }}"
                                            >
                                                Ver solicitud
                                            </button>

                                            {{-- Aprobar (sólo si la CUENTA está pending) --}}
                                            @if($accountStatus === 'pending')
                                                <form method="POST" action="{{ route('admin.users.approve',$u) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm">Aprobar</button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.users.reject',$u) }}" class="d-inline ms-2">
                                                    @csrf
                                                    <div class="input-group input-group-sm" style="max-width: 340px;">
                                                        <input name="reason" class="form-control" placeholder="Motivo (opcional)">
                                                        <button class="btn btn-danger">Rechazar</button>
                                                    </div>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- MODAL: Ver solicitud --}}
                                    <div class="modal fade" id="user-request-{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg position-relative">
                                                <button type="button" class="btn-close position-absolute end-0 top-0 m-3 z-3"
                                                        data-bs-dismiss="modal" aria-label="Cerrar"></button>

                                                <div class="modal-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <div>
                                                            <div class="h5 mb-1">{{ $u->name }}</div>
                                                            <div class="text-muted small">
                                                                #{{ $u->id }} · {{ $u->email }} · Rol: {{ $u->role }}
                                                            </div>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <span class="badge {{ $accountClass }}">cuenta: {{ $accountStatus !== '' ? $accountStatus : '—' }}</span>
                                                            <span class="badge {{ $profileClass }}">perfil: {{ $profileStatus !== '' ? $profileStatus : '—' }}</span>
                                                        </div>
                                                    </div>

                                                    @if($pendingEdit || ($profile && $profile->exists))
                                                        <div class="row g-3">
                                                            <div class="col-md-8">
                                                                <div class="mb-3">
                                                                    <div class="text-uppercase text-muted small">Nombre público</div>
                                                                    <div class="fw-semibold">{{ $displayName ?? '—' }}</div>
                                                                </div>

                                                                <div class="row g-3">
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Especialidad</div>
                                                                        <div class="fw-semibold">{{ $serviceName ?? '—' }}</div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Modalidad</div>
                                                                        <div>
                                                                            @if($mode)
                                                                                <span class="badge text-bg-info">{{ $mode }}</span>
                                                                            @else
                                                                                <span class="text-muted">—</span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row g-3 mt-1">
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">WhatsApp</div>
                                                                        <div class="fw-semibold">{{ $whatsapp ?? '—' }}</div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Correo</div>
                                                                        <div class="fw-semibold">{{ $contactEmail ?? '—' }}</div>
                                                                    </div>
                                                                </div>

                                                                <div class="row g-3 mt-1">
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">País</div>
                                                                        <div class="fw-semibold">{{ $country ?? '—' }}</div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Provincia/Estado</div>
                                                                        <div class="fw-semibold">{{ $state ?? '—' }}</div>
                                                                    </div>
                                                                </div>

                                                                <div class="row g-3 mt-1">
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Ciudad</div>
                                                                        <div class="fw-semibold">{{ $city ?? '—' }}</div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="text-uppercase text-muted small">Dirección</div>
                                                                        <div class="fw-semibold">{{ $address ?? '—' }}</div>
                                                                    </div>
                                                                </div>

                                                                @if($about)
                                                                    <div class="mt-3">
                                                                        <div class="text-uppercase text-muted small mb-1">Detalle (propuesto)</div>
                                                                        <div class="small border rounded p-2" style="max-height:12rem;overflow:auto;">
                                                                            {!! nl2br(e($about)) !!}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="col-md-4">
                                                                <div class="mb-3">
                                                                    <div class="text-uppercase text-muted small">Foto (propuesta)</div>
                                                                    @if($photoPath)
                                                                        <img src="{{ asset('storage/'.$photoPath) }}" class="img-fluid rounded border">
                                                                    @else
                                                                        <div class="text-muted">—</div>
                                                                    @endif
                                                                </div>

                                                                @if($videoUrl)
                                                                    <div class="text-uppercase text-muted small">Video</div>
                                                                    <div class="ratio ratio-16x9">
                                                                        <iframe
                                                                            src="{{ $videoUrl }}"
                                                                            title="Video del proveedor"
                                                                            allowfullscreen
                                                                            class="rounded border">
                                                                        </iframe>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-warning">
                                                            El usuario todavía no cargó datos de perfil.
                                                        </div>
                                                    @endif

                                                    {{-- Acciones dentro del modal --}}
                                                    <div class="d-flex flex-wrap gap-2 mt-4">
                                                        @if($accountStatus === 'pending')
                                                            <form method="POST" action="{{ route('admin.users.approve',$u) }}">
                                                                @csrf
                                                                <button class="btn btn-success">Aprobar</button>
                                                            </form>

                                                            <form method="POST" action="{{ route('admin.users.reject',$u) }}" class="d-flex align-items-center gap-2">
                                                                @csrf
                                                                <input name="reason" class="form-control form-control-sm" style="min-width:260px" placeholder="Motivo (opcional)">
                                                                <button class="btn btn-danger">Rechazar</button>
                                                            </form>
                                                        @else
                                                            <span class="text-muted small">
                                                                Acciones disponibles sólo para cuentas en estado <b>pending</b>.
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- /MODAL --}}
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $pending->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sin bloque de "Cuentas suspendidas" --}}
        </div>
    </div>
</x-app-layout>
