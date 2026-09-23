<fieldset class="mt-3 mb-3" id="unit-picker" data-required-count="{{ $requiredUnitCount ?? '' }}">
    <legend class="h6">Available Units</legend>
    <input type="hidden" name="select_units" value="1">
    <div id="unit-picker-options" class="row g-2"></div>
    <div id="unit-picker-count" class="small mt-2" role="status" aria-live="polite"></div>
    @error('unit_ids')<div class="text-danger small" data-ajax-error>{{ $message }}</div>@enderror
</fieldset>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const property = document.getElementById('property_id');
    const picker = document.getElementById('unit-picker');
    const options = document.getElementById('unit-picker-options');
    const countLabel = document.getElementById('unit-picker-count');
    const quantity = document.getElementById('quantity_assigned');
    let selectedIds = @json(array_values(array_map('strval', array_filter(is_array($selectedUnitIds) ? $selectedUnitIds : [], 'is_scalar'))));
    const updateCount = () => {
        const checked = options.querySelectorAll('input:checked').length;
        const required = quantity ? Number(quantity.value || 0) : Number(picker.dataset.requiredCount);
        countLabel.textContent = checked + ' / ' + required + ' selected';
    };
    const render = () => {
        options.replaceChildren();
        const units = JSON.parse(property.selectedOptions[0]?.dataset.units || '[]');
        for (const unit of units) {
            const column = document.createElement('div');
            column.className = 'col-sm-6';
            const label = document.createElement('label');
            label.className = 'border rounded-3 p-3 d-flex gap-2 align-items-center h-100';
            label.style.cursor = 'pointer';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'unit_ids[]';
            checkbox.value = unit.id;
            checkbox.className = 'form-check-input m-0 flex-shrink-0';
            checkbox.checked = selectedIds.includes(String(unit.id));
            const serial = document.createElement('span');
            serial.className = 'text-break';
            serial.textContent = unit.serial_number;
            label.append(checkbox, serial);
            column.append(label);
            options.append(column);
        }
        if (property.value && units.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'text-muted small';
            empty.textContent = 'No available units.';
            options.append(empty);
        }
        updateCount();
    };
    property.addEventListener('change', () => { selectedIds = []; render(); });
    options.addEventListener('change', () => {
        if (quantity) {
            quantity.value = options.querySelectorAll('input:checked').length;
            quantity.dispatchEvent(new Event('input'));
        }
        updateCount();
    });
    quantity?.addEventListener('input', updateCount);
    render();
});
</script>
@endpush
