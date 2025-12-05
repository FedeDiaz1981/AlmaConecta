@extends('layouts.app')

@section('title', 'Cambios pendientes')

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
                        <h2 class="h5 mb-0">Cambios pendientes</h2>
                        <span class="badge bg-secondary">
                            {{ $edits->total() ?? $edits->count() }} pendientes
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    @if($edits->isEmpty())
                        <p class="mb-0">No hay pendientes.</p>
                    @else
                        @foreach($edits as $e)
                            @php
                                $profile = $e->profile;

                                // Normalizar payload
                                $payload = is_array($e->payload) ? $e->payload : (json_decode($e->payload, true) ?? []);

                                // Valores actuales (aprobados)
                                $current = [
                                    'display_name'    => $profile->display_name,
                                    'service_id'      => $profile->service_id,
                                    'about'           => $profile->about,
                                    'video_url'       => $profile->video_url,
                                    'template_key'    => $profile->template_key,
                                    'mode_remote'     => (bool)$profile->mode_remote,
                                    'mode_presential' => (bool)$profile->mode_presential,
                                    'country'         => $profile->country,
                                    'state'           => $profile->state,
                                    'city'            => $profile->city,
                                    'address'         => $profile->address,
                                    'lat'             => $profile->lat,
                                    'lng'             => $profile->lng,
                                    'whatsapp'        => $profile->whatsapp,
                                    'contact_email'   => $profile->contact_email,
                                    'photo_path'      => $profile->photo_path,
                                ];

                                // Etiquetas
                                $labels = [
                                    'display_name'    => 'Nombre público',
                                    'service_id'      => 'Especialidad',
                                    'about'           => 'Detalle',
                                    'video_url'       => 'Video (URL)',
                                    'template_key'    => 'Template',
                                    'mode'            => 'Modalidad',
                                    'country'         => 'País (ISO-2)',
                                    'state'           => 'Provincia/Estado',
                                    'city'            => 'Ciudad',
                                    'address'         => 'Dirección',
                                    'lat'             => 'Latitud',
                                    'lng'             => 'Longitud',
                                    'whatsapp'        => 'WhatsApp',
                                    'contact_email'   => 'Correo',
                                    'photo_path'      => 'Foto',
                                ];

                                // Modalidad actual y propuesta
                                $curMode = $current['mode_remote'] && $current['mode_presential']
                                    ? 'Remoto y presencial'
                                    : ($current['mode_remote'] ? 'Remoto' : ($current['mode_presential'] ? 'Presencial' : '—'));

                                $newMode = null;
                                if (array_key_exists('mode_remote', $payload) || array_key_exists('mode_presential', $payload)) {
                                    $nr = (bool)($payload['mode_remote'] ?? $current['mode_remote']);
                                    $np = (bool)($payload['mode_presential'] ?? $current['mode_presential']);
                                    $newMode = $nr && $np ? 'Remoto y presencial' : ($nr ? 'Remoto' : ($np ? 'Presencial' : '—'));
                                }

                                // Resolver nombre de servicio
                                $serviceName = function($serviceId) {
                                    if (!$serviceId) return '—';
                                    $s = \App\Models\Service::find($serviceId);
                                    return $s?->name ?? '—';
                                };

                                // Campos a comparar (excepto "about" y "photo_path" que los tratamos aparte)
                                $fields = [
                                    'display_name' => [
                                        'current' => $current['display_name'],
                                        'new'     => $payload['display_name'] ?? null,
                                    ],
                                    'service_id' => [
                                        'current' => $serviceName($current['service_id']),
                                        'new'     => isset($payload['service_id']) ? $serviceName($payload['service_id']) : null,
                                    ],
                                    'mode' => [
                                        'current' => $curMode,
                                        'new'     => $newMode,
                                    ],
                                    'country' => ['current'=>$current['country'], 'new'=>$payload['country'] ?? null],
                                    'state'   => ['current'=>$current['state'],   'new'=>$payload['state']   ?? null],
                                    'city'    => ['current'=>$current['city'],    'new'=>$payload['city']    ?? null],
                                    'address' => ['current'=>$current['address'], 'new'=>$payload['address'] ?? null],
                                    'whatsapp'      => ['current'=>$current['whatsapp'],      'new'=>$payload['whatsapp']      ?? null],
                                    'contact_email' => ['current'=>$current['contact_email'], 'new'=>$payload['contact_email'] ?? null],
                                    'video_url'     => ['current'=>$current['video_url'],     'new'=>$payload['video_url']     ?? null],
                                    'template_key'  => ['current'=>$current['template_key'],  'new'=>$payload['template_key']  ?? null],
                                ];

                                // Filtrar cambios reales
                                $changes = [];
                                foreach ($fields as $key => $pair) {
                                    $cur = $pair['current'];
                                    $new = $pair['new'];
                                    if ($new === null) continue;
                                    if ((string)$cur !== (string)$new) {
                                        $changes[$key] = $pair;
                                    }
                                }

                                // About y Foto cambiaron?
                                $aboutChanged = array_key_exists('about', $payload)
                                    && (string)($current['about'] ?? '') !== (string)($payload['about'] ?? '');

                                $photoChanged = array_key_exists('photo_path', $payload)
                                    && (string)($current['photo_path'] ?? '') !== (string)($payload['photo_path'] ?? '');
                            @endphp

                            <div class="card mb-4 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $profile->display_name }}</strong>
                                        <span class="text-muted"> (ID {{ $e->profile_id }})</span>
                                        <span class="badge text-bg-warning ms-2">pendiente</span>
                                    </div>

                                    <div class="d-flex gap-2">
                                        {{-- Ver perfil público en modal --}}
                                        <button class="btn btn-outline-secondary btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#preview-{{ $e->id }}">
                                            Ver perfil
                                        </button>
                                        <span class="text-muted small">{{ $e->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                <div class="card-body">
                                    @if(empty($changes) && !$aboutChanged && !$photoChanged)
                                        <div class="alert alert-info mb-0">
                                            No se detectaron cambios relevantes.
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 220px;">Campo</th>
                                                        <th>Valor aprobado</th>
                                                        <th>Propuesto</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($changes as $key => $pair)
                                                        <tr class="table-warning">
                                                            <td class="fw-semibold">
                                                                {{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}
                                                            </td>
                                                            <td class="text-muted">
                                                                {{ ($pair['current'] === '' || $pair['current'] === null) ? '—' : $pair['current'] }}
                                                            </td>
                                                            <td class="fw-semibold">
                                                                {{ ($pair['new'] === '' || $pair['new'] === null) ? '—' : $pair['new'] }}
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    @if($aboutChanged)
                                                        <tr class="table-warning">
                                                            <td class="fw-semibold">{{ $labels['about'] }}</td>
                                                            <td class="text-muted">
                                                                <div class="small" style="max-height:6rem;overflow:auto;">
                                                                    {!! nl2br(e(\Illuminate\Support\Str::limit(trim(strip_tags($current['about'] ?? '—')), 1200))) !!}
                                                                </div>
                                                            </td>
                                                            <td class="fw-semibold">
                                                                <div class="small" style="max-height:6rem;overflow:auto;">
                                                                    {!! nl2br(e(\Illuminate\Support\Str::limit(trim(strip_tags($payload['about'] ?? '—')), 1200))) !!}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    @if($photoChanged)
                                                        <tr class="table-warning">
                                                            <td class="fw-semibold">{{ $labels['photo_path'] }}</td>
                                                            <td>
                                                                @if($current['photo_path'])
                                                                    <img src="{{ asset('storage/'.$current['photo_path']) }}"
                                                                         class="img-thumbnail"
                                                                         style="max-height:100px">
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(!empty($payload['photo_path']))
                                                                    <img src="{{ asset('storage/'.$payload['photo_path']) }}"
                                                                         class="img-thumbnail border border-success"
                                                                         style="max-height:100px">
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    {{-- Acciones --}}
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <form method="POST" action="{{ route('admin.edits.approve', $e) }}">
                                            @csrf
                                            <button class="btn btn-success">
                                                Aprobar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.edits.reject', $e) }}" class="d-flex align-items-center gap-2">
                                            @csrf
                                            <input name="reason" placeholder="Motivo (opcional)"
                                                   class="form-control form-control-sm" style="min-width: 220px;">
                                            <button class="btn btn-danger">
                                                Rechazar
                                            </button>
                                        </form>

                                        <button class="btn btn-outline-secondary btn-sm ms-auto"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#raw-{{ $e->id }}">
                                            Ver JSON
                                        </button>
                                    </div>

                                    <div class="collapse mt-3" id="raw-{{ $e->id }}">
                                        <pre class="bg-light p-2 small rounded border">
{{ json_encode($e->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
                                        </pre>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal de preview del perfil público --}}
                            <div class="modal fade" id="preview-{{ $e->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Vista del perfil: {{ $profile->display_name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body p-0" style="height: 75vh;">
                                            <iframe
                                                src="{{ route('profiles.show', $profile->slug) }}"
                                                title="Preview perfil"
                                                class="w-100 h-100 border-0"
                                                referrerpolicy="no-referrer"
                                            ></iframe>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-3">
                            {{ $edits->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
