@extends('layouts.app')

@section('title', 'Panel de administración')

@section('content')
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6">

                @php
                    /** @var \Illuminate\Support\Collection $users */
                    $users = $users ?? collect();
                    $pendingCount = $pendingCount ?? 0;
                @endphp

                {{-- Título + botones principales --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-semibold text-silver">
                            Panel de administración · Usuarios activos / suspendidos
                        </h2>
                        <span
                            class="inline-flex items-center justify-center text-xs px-2 py-1 rounded-full bg-blueDeep text-silver/80">
                            {{ $users->count() }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Pendientes de aprobación (cuentas nuevas + edits) --}}
                        <a href="{{ route('admin.approvals.index') }}"
                           class="inline-flex items-center px-4 py-2 rounded-xl bg-gold text-blueDeep text-sm font-semibold hover:bg-goldStrong transition">
                            Pendientes de aprobación
                            <span
                                class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-blueDeep text-gold text-xs">
                                {{ $pendingCount }}
                            </span>
                        </a>

                        {{-- ABM de especialidades --}}
                        <a href="{{ route('admin.specialties.index') }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blueMid text-silver text-sm font-semibold hover:bg-blueNight transition">
                            Gestionar especialidades
                        </a>
                    </div>
                </div>

                @if ($users->isEmpty())
                    <p class="text-silver/70 text-sm">
                        No hay usuarios activos ni suspendidos para mostrar.
                    </p>
                @else
                    <div class="overflow-x-auto rounded-xl border border-blueMid/60">
                        <table class="min-w-full divide-y divide-blueMid/60 text-sm">
                            <thead class="bg-blueDeep/80 text-silver/80">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">ID</th>
                                    <th class="px-4 py-2 text-left font-semibold">Nombre</th>
                                    <th class="px-4 py-2 text-left font-semibold">Email</th>
                                    <th class="px-4 py-2 text-left font-semibold">Rol</th>
                                    <th class="px-4 py-2 text-left font-semibold">Alta</th>
                                    <th class="px-4 py-2 text-left font-semibold">Estado</th>
                                    <th class="px-4 py-2 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-blueMid/40 bg-blueDeep/60">
                                @foreach ($users as $u)
                                    @php
                                        $status = strtolower((string) ($u->account_status ?? 'unknown'));
                                        $isSuspended = $status === 'suspended';

                                        $roleClasses = match ($u->role) {
                                            'admin'    => 'bg-blue-900 text-silver',
                                            'provider' => 'bg-cyan-200 text-blueDeep',
                                            default    => 'bg-slate-500 text-silver',
                                        };

                                        $statusClasses = match ($status) {
                                            'active'    => 'bg-emerald-500/90 text-blueDeep',
                                            'suspended' => 'bg-amber-400 text-blueDeep',
                                            'pending'   => 'bg-slate-500 text-silver',
                                            'rejected'  => 'bg-red-500 text-silver',
                                            default     => 'bg-slate-700 text-silver',
                                        };

                                        $slug = \App\Models\Profile::where('user_id', $u->id)->value('slug');
                                    @endphp

                                    <tr>
                                        <td class="px-4 py-2 text-silver/90">{{ $u->id }}</td>
                                        <td class="px-4 py-2 text-silver font-semibold">{{ $u->name }}</td>
                                        <td class="px-4 py-2 text-silver/80">{{ $u->email }}</td>

                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $roleClasses }}">
                                                {{ $u->role }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2 text-silver/80">
                                            {{ optional($u->created_at)->format('Y-m-d') }}
                                        </td>

                                        <td class="px-4 py-2">
                                            <span
                                                class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                                {{ $status }}
                                            </span>
                                        </td>

                                        <td class="px-4 py-2">
                                            <div class="flex flex-wrap items-center justify-center gap-2">

                                                {{-- Ver perfil público (no mostrar para admins) --}}
                                                @if ($slug && $u->role !== 'admin')
                                                    <a href="{{ route('profiles.show', ['slug' => $slug]) }}"
                                                       target="_blank"
                                                       class="px-3 py-1.5 rounded-lg border border-blueMid text-xs text-silver/90 hover:bg-blueMid/60 transition">
                                                        Ver perfil
                                                    </a>
                                                @elseif($u->role === 'admin')
                                                    <span
                                                        class="px-3 py-1.5 rounded-lg border border-blueMid/40 text-xs text-silver/70 cursor-default">
                                                        Cuenta admin
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-3 py-1.5 rounded-lg border border-blueMid/40 text-xs text-silver/40 cursor-not-allowed">
                                                        Sin perfil
                                                    </span>
                                                @endif

                                                {{-- Suspender --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.suspend', $u) }}"
                                                      onsubmit="return confirm('¿Suspender la cuenta de {{ $u->name }}?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                                                   {{ $isSuspended ? 'bg-amber-500/30 text-amber-200 cursor-not-allowed' : 'bg-amber-500 text-blueDeep hover:bg-amber-400' }}"
                                                            {{ $isSuspended ? 'disabled' : '' }}>
                                                        Suspender
                                                    </button>
                                                </form>

                                                {{-- Activar --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.activate', $u) }}"
                                                      onsubmit="return confirm('¿Activar la cuenta de {{ $u->name }}?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                                                   {{ $isSuspended ? 'bg-emerald-500 text-blueDeep hover:bg-emerald-400' : 'bg-emerald-500/30 text-emerald-200 cursor-not-allowed' }}"
                                                            {{ $isSuspended ? '' : 'disabled' }}>
                                                        Activar
                                                    </button>
                                                </form>

                                                {{-- Eliminar --}}
                                                <form method="POST"
                                                      action="{{ route('admin.users.destroy', $u) }}"
                                                      onsubmit="return confirm('Esta acción es PERMANENTE. ¿Eliminar definitivamente la cuenta de {{ $u->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-600 text-silver hover:bg-red-500">
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

                    <p class="text-xs text-silver/60 mt-3">
                        Podés usar el buscador del navegador (Ctrl+F / Cmd+F) para buscar por nombre, email, etc.
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
