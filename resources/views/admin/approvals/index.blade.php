@extends('layouts.app')

@section('title', 'Pendientes de aprobación')

@section('content')
<div class="py-8 bg-blueDeep min-h-[60vh]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <div>
            <h1 class="text-2xl font-semibold text-silver">
                Pendientes de aprobación
            </h1>
            <p class="text-sm text-silver/70 mt-1">
                En esta pantalla ves, en un solo lugar, las cuentas nuevas y los cambios de perfiles que requieren revisión.
            </p>
        </div>

        {{-- RESUMEN --}}
        <div class="grid gap-4 md:grid-cols-2">
            <div class="bg-blueNight/80 border border-blueMid rounded-2xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-silver/60">Cuentas nuevas</p>
                        <p class="text-2xl font-semibold text-silver mt-1">{{ $pendingUsers->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-blueNight/80 border border-blueMid rounded-2xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-silver/60">Cambios de perfil</p>
                        <p class="text-2xl font-semibold text-silver mt-1">{{ $pendingEdits->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRILLA UNIFICADA (simple: dos bloques en la misma vista) --}}
        <div class="bg-blueNight/80 border border-blueMid rounded-2xl shadow-soft">
            <div class="border-b border-blueMid/70 px-4 py-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-silver tracking-wide uppercase">
                    Cuentas nuevas
                </h2>
            </div>

            <div class="p-4">
                @if($pendingUsers->isEmpty())
                    <p class="text-sm text-silver/70">No hay cuentas nuevas pendientes.</p>
                @else
                    <div class="overflow-x-auto rounded-xl border border-blueMid/60">
                        <table class="min-w-full text-sm divide-y divide-blueMid/60">
                            <thead class="bg-blueDeep/80 text-silver/70">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">ID</th>
                                    <th class="px-3 py-2 text-left font-semibold">Nombre</th>
                                    <th class="px-3 py-2 text-left font-semibold">Email</th>
                                    <th class="px-3 py-2 text-left font-semibold">Estado cuenta</th>
                                    <th class="px-3 py-2 text-left font-semibold">Estado perfil</th>
                                    <th class="px-3 py-2 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blueMid/40 bg-blueDeep/60">
                                @foreach($pendingUsers as $u)
                                    @php
                                        $accountStatus = strtolower((string)($u->account_status ?? $u->status ?? 'pending'));
                                        $accountClasses = match ($accountStatus) {
                                            'active'    => 'bg-emerald-500/90 text-blueDeep',
                                            'suspended' => 'bg-slate-500 text-silver',
                                            'pending'   => 'bg-amber-400 text-blueDeep',
                                            'rejected'  => 'bg-red-500 text-silver',
                                            default     => 'bg-slate-700 text-silver',
                                        };

                                        $profile = \App\Models\Profile::where('user_id', $u->id)->first();
                                        $profileStatus = strtolower((string)($profile->status ?? ''));
                                        $profileClasses = match ($profileStatus) {
                                            'approved'  => 'bg-emerald-500/90 text-blueDeep',
                                            'pending'   => 'bg-amber-400 text-blueDeep',
                                            'rejected'  => 'bg-red-500 text-silver',
                                            default     => 'bg-slate-700 text-silver',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-silver/90">{{ $u->id }}</td>
                                        <td class="px-3 py-2 text-silver">{{ $u->name }}</td>
                                        <td class="px-3 py-2 text-silver/80">{{ $u->email }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $accountClasses }}">
                                                {{ $accountStatus ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $profileClasses }}">
                                                {{ $profileStatus ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2 justify-center">
                                                {{-- reusar lógica existente --}}
                                                <form method="POST" action="{{ route('admin.users.approve', $u) }}">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg bg-emerald-500 text-blueDeep text-xs font-semibold hover:bg-emerald-400">
                                                        Aprobar
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.users.reject', $u) }}">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg bg-red-600 text-silver text-xs font-semibold hover:bg-red-500">
                                                        Rechazar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-blueNight/80 border border-blueMid rounded-2xl shadow-soft">
            <div class="border-b border-blueMid/70 px-4 py-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-silver tracking-wide uppercase">
                    Cambios de perfil
                </h2>
            </div>

            <div class="p-4">
                @if($pendingEdits->isEmpty())
                    <p class="text-sm text-silver/70">No hay cambios de perfil pendientes.</p>
                @else
                    <div class="overflow-x-auto rounded-xl border border-blueMid/60">
                        <table class="min-w-full text-sm divide-y divide-blueMid/60">
                            <thead class="bg-blueDeep/80 text-silver/70">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold">ID</th>
                                    <th class="px-3 py-2 text-left font-semibold">Profesional</th>
                                    <th class="px-3 py-2 text-left font-semibold">Perfil</th>
                                    <th class="px-3 py-2 text-left font-semibold">Fecha</th>
                                    <th class="px-3 py-2 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blueMid/40 bg-blueDeep/60">
                                @foreach($pendingEdits as $edit)
                                    @php
                                        $profile = $edit->profile;
                                        $user    = $profile?->user;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 text-silver/90">{{ $edit->id }}</td>
                                        <td class="px-3 py-2 text-silver">
                                            {{ $user?->name ?? '—' }}<br>
                                            <span class="text-xs text-silver/60">{{ $user?->email }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-silver/80">
                                            {{ $profile?->display_name ?? '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-silver/70">
                                            {{ $edit->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-wrap gap-2 justify-center">
                                                {{-- aprobar cambio, usando la ruta que ya tenías --}}
                                                <form method="POST" action="{{ route('admin.edits.approve', $edit) }}">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg bg-emerald-500 text-blueDeep text-xs font-semibold hover:bg-emerald-400">
                                                        Aprobar
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.edits.reject', $edit) }}">
                                                    @csrf
                                                    <button class="px-3 py-1.5 rounded-lg bg-red-600 text-silver text-xs font-semibold hover:bg-red-500">
                                                        Rechazar
                                                    </button>
                                                </form>

                                                {{-- link para ver detalle pesado que ya tenías en la vista vieja --}}
                                                <a href="{{ route('admin.edits.index') }}"
                                                   class="px-3 py-1.5 rounded-lg border border-blueMid text-xs text-silver/80 hover:bg-blueMid/40">
                                                    Ver detalle completo
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
