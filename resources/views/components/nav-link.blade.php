@props(['active'])

@php
$classes = $active ? 'nav-link active': 'nav-link';
@endphp

<li class="nav-item">
    <a aria-current="page" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
</li>