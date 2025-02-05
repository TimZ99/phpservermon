@props(['messages'])

@if ($messages)
    <ul class="text-sm text-danger">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
