@props([
    'outline' => false,
    'disabled' => false
])
@php
$class = $outline ? 'btn-outline-danger' : 'btn-danger'
@endphp

<button @disabled($disabled) {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'btn px-4 py-2 ' . $class
    ]) }}>
    {{ $slot }}
</button>
