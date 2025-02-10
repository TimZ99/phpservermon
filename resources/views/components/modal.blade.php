@props([
    'id',
    'dataBsBackdrop' => 'static',
    'dataBsKeyboard' => 'false',
    'ariaLabelledby' => null,
    'ariaHidden' => true,
    'modalDialogCentered' => true
])

<div class="modal"
    {{ $attributes->merge([
        'id' => $id,
        'tabindex' => '-1',
        'data-bs-backdrop' => $dataBsBackdrop,
        'data-bs-keyboard' => $dataBsKeyboard,
        'aria-labelledby' => $ariaLabelledby,
        'aria-hidden' => $ariaHidden
    ]) }}>
  <div {{ $attributes->merge([
        'class' => 'modal-dialog' . ($modalDialogCentered ? ' modal-dialog-centered' : '')
    ]) }}>
    <div class="modal-content">
      {{ $slot }}
    </div>
  </div>
</div>
