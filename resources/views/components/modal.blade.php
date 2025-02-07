@props([
    'id',
])

<div class="modal" id="{{ $id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      {{ $slot }}
    </div>
  </div>
</div>
