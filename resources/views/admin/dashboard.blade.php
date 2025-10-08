<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Dashboard</h2>
    </x-slot>

    {{-- Bootstrap 5 + DataTables (Bootstrap 5 + Responsive) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <div class="py-4">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="h5 mb-0">Usuarios activos / suspendidos</h3>
                        <span class="badge bg-secondary">
                            {{ isset($users) ? $users->count() : 0 }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @php
                        // 1) Si NO viene $users desde el controlador, lo obtenemos acá con account_status incluido
                        if (!isset($users)) {
                            $users = \App\Models\User::query()
                                ->whereIn('account_status', ['active', 'suspended'])
                                ->select('id','name','email','role','account_status','created_at')
                                ->orderByDesc('id')
                                ->get();
                        }

                        // 2) Traer SIEMPRE el estado real desde DB para los IDs listados,
                        //    por si el controlador no incluyó el campo o hay accessor por defecto.
                        $statusMap = \App\Models\User::query()
                            ->whereIn('id', $users->pluck('id'))
                            ->pluck('account_status','id'); // [id => 'active'|'suspended'|...]
                    @endphp

                    <div class="table-responsive">
                        <table id="users-table" class="table table-striped table-hover align-middle nowrap" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Alta</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                    @php
                                        // Estado REAL (DB) con fallback a lo que venga en el modelo
                                        $status = strtolower((string)($statusMap[$u->id] ?? $u->account_status ?? ''));
                                        if ($status === '') { $status = 'unknown'; }

                                        $isSuspended = $status === 'suspended';

                                        $roleBadge = match($u->role) {
                                            'admin'    => 'badge bg-dark text-white',
                                            'provider' => 'badge bg-info text-dark',
                                            default    => 'badge bg-secondary text-white',
                                        };

                                        $statusBadge = match($status) {
                                            'active'    => 'badge bg-success',
                                            'suspended' => 'badge bg-warning text-dark',
                                            'pending'   => 'badge bg-secondary',
                                            'rejected'  => 'badge bg-danger',
                                            default     => 'badge bg-light text-dark',
                                        };

                                        // slug del perfil (si existe)
                                        $slug = \App\Models\Profile::where('user_id', $u->id)->value('slug');
                                    @endphp
                                    <tr>
                                        <td>{{ $u->id }}</td>
                                        <td class="fw-semibold">{{ $u->name }}</td>
                                        <td>{{ $u->email }}</td>
                                        <td><span class="{{ $roleBadge }}">{{ $u->role }}</span></td>
                                        <td>{{ optional($u->created_at)->format('Y-m-d') }}</td>
                                        <td><span class="{{ $statusBadge }}">{{ $status }}</span></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center flex-wrap">

                                                {{-- Ver perfil (público). Se abre en nueva pestaña --}}
                                                @if($slug)
                                                    <a href="{{ route('profiles.show', ['slug' => $slug]) }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-primary">
                                                        Ver perfil
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Este usuario aún no tiene perfil">
                                                        Ver perfil
                                                    </button>
                                                @endif

                                                {{-- Suspender --}}
                                                <form method="POST" action="{{ route('admin.users.suspend', $u) }}"
                                                      onsubmit="return confirm('¿Suspender la cuenta de {{ $u->name }}? El usuario no podrá ingresar hasta que sea activado.');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-warning {{ $isSuspended ? 'disabled' : '' }}"
                                                            {{ $isSuspended ? 'disabled' : '' }}>
                                                        Suspender
                                                    </button>
                                                </form>

                                                {{-- Activar --}}
                                                <form method="POST" action="{{ route('admin.users.activate', $u) }}"
                                                      onsubmit="return confirm('¿Activar la cuenta de {{ $u->name }}?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-success {{ $isSuspended ? '' : 'disabled' }}"
                                                            {{ $isSuspended ? '' : 'disabled' }}>
                                                        Activar
                                                    </button>
                                                </form>

                                                {{-- Eliminar (REST: DELETE) --}}
                                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                      onsubmit="return confirm('Esta acción es PERMANENTE. ¿Eliminar definitivamente la cuenta de {{ $u->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-muted small mt-3">
                        * Usá el cuadro de búsqueda de arriba a la derecha para filtrar por cualquier campo.
                        Hacé click en los encabezados para ordenar.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS: jQuery (para DataTables), Bootstrap, DataTables --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(function () {
            $('#users-table').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });
        });
    </script>
</x-app-layout>
