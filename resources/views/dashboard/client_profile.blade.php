@extends('layouts.app')

@section('title', 'Mi cuenta')

@section('content')
    <div class="py-10 bg-blueDeep">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="font-semibold text-2xl text-silver">Mi cuenta</h1>
                <p class="mt-1 text-sm text-silver/70">
                    Administrá tus datos de contacto y acceso.
                </p>
            </div>

            <div class="bg-blueNight/80 border border-blueMid shadow-soft rounded-2xl p-6 sm:p-8 space-y-6">
                @if ($errors->any())
                    <div class="mb-2 rounded-xl border border-red-500/60 bg-red-900/40 px-4 py-3 text-sm text-red-100">
                        <div class="font-semibold mb-1">Revisá estos campos:</div>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-4 sm:p-6 rounded-xl border border-blueMid/60 bg-blueDeep/60">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="p-4 sm:p-6 rounded-xl border border-blueMid/60 bg-blueDeep/60">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="p-4 sm:p-6 rounded-xl border border-red-500/40 bg-red-900/20">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
