import './bootstrap';

document.addEventListener("DOMContentLoaded", function() {
    const popularPortsSelect = document.getElementById('popular_ports');
    const portInput = document.getElementById('port');
    const portLabel = document.querySelector('label[for="port"]');

    popularPortsSelect.addEventListener("change", function() {
        if (popularPortsSelect.value === 'custom') {
            portInput.classList.remove('d-none');
            portLabel.classList.remove('d-none');
            portInput.focus();
        } else {
            portInput.value = popularPortsSelect.value;
            portInput.classList.add('d-none');
            portLabel.classList.add('d-none');
        }
    });

    // Trigger the change event manually to set the initial state
    popularPortsSelect.dispatchEvent(new Event('change'));
});