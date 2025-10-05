<div class="bg-white p-0 rounded shadow overflow-hidden">
    <div class="bg-gray-200 aspect-video">
        @if($profile->media->first() && $profile->media->first()->type==='video')
            <iframe src="{{ $profile->media->first()->url }}" class="w-full h-full" allowfullscreen></iframe>
        @endif
    </div>
    <div class="p-6">
        <h1 class="text-3xl font-bold">{{ $profile->display_name }}</h1>
        <p class="mt-2 text-gray-700">{{ $profile->about }}</p>
        <div class="mt-3">
            @foreach($profile->services as $s)
                <span class="inline-block text-xs bg-gray-100 px-2 py-1 rounded mr-1 mb-1">{{ $s->name }}</span>
            @endforeach
        </div>
    </div>
</div>
