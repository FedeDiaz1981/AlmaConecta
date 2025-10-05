@component('mail::message')
@foreach($lines as $line)
{{ $line }}

@endforeach
@endcomponent
