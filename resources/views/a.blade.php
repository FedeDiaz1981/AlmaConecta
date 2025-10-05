<div class="bg-white p-6 rounded shadow grid md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        @if($profile->media->first())
            @if($profile->media->first()->type==='video')
                <div class="aspect-video">
                    <iframe src="{{ $profile->media->first()->url }}" class="w-full h-full" allowfullscreen></iframe>
                </div>
            @else
                <img src="{{ $profile->media->first()->url }}" class="rounded w-full" alt="">
            @endif
        @endif
    </div>
    <div class="md:col-span-2">
        <h1 class="text-2xl font-bold">{{ $profile->display_name }}</h1>
        <div class="text-gray-600">{{ $profile->city }}, {{ $profile->state }}</div>
        <div class="mt-2">
            @if($profile->mode_presential) <span class="text-xs bg-blue-100 px-2 py-1 rounded">Presencial</span> @endif
            @if($profile->mode_remote) <span class="text-xs bg-green-100 px-2 py-1 rounded">Remoto</span> @endif
        </div>
        <div class="mt-4">{{ $profile->about }}</div>
        @if($profile->lat && $profile->lng)
            <div class="mt-4">
                <iframe width="100%" height="250" style="border:0" loading="lazy" allowfullscreen
                    src="https://www.google.com/maps?q={{ $profile->lat }},{{ $profile->lng }}&z=14&output=embed"></iframe>
            </div>
        @endif
    </div>
</div>
