@php
    $availableUnits = $property->exists ? $property->availableUnits()->get() : collect();
    $serialRows = old('units', $availableUnits->map(fn ($unit) => ['id' => $unit->id, 'serial_number' => $unit->serial_number])->all());
    $serialRows = is_array($serialRows) ? $serialRows : [];
@endphp
<fieldset>
    <legend class="h6 mb-3">Unit Serial Numbers</legend>
    <div id="inventory-unit-fields" class="row g-3">
        @for ($index = 0; $index < $selectedQuantity; $index++)
            <div class="col-md-6" data-unit-row>
                <label for="unit-serial-{{ $index }}" class="form-label">Unit {{ $index + 1 }} — Serial Number</label>
                <input type="hidden" name="units[{{ $index }}][id]" value="{{ $serialRows[$index]['id'] ?? '' }}">
                <input type="text" id="unit-serial-{{ $index }}" name="units[{{ $index }}][serial_number]" maxlength="255"
                    class="form-control @error('units.'.$index.'.serial_number') is-invalid @enderror"
                    value="{{ $serialRows[$index]['serial_number'] ?? '' }}">
                @error('units.'.$index.'.serial_number')<div class="invalid-feedback" data-ajax-error>{{ $message }}</div>@enderror
            </div>
        @endfor
    </div>
    @error('units')<div class="text-danger small">{{ $message }}</div>@enderror
</fieldset>
@if ($property->exists)
    @php($otherUnits = $property->units()->where('status', '!=', 'available')->orderBy('id')->get())
    @if ($otherUnits->isNotEmpty())
        <div class="mt-3 d-flex flex-wrap gap-2">
            @foreach ($otherUnits as $unit)
                <span class="badge text-bg-light border text-dark text-wrap">{{ $unit->serial_number }} · {{ ucfirst($unit->status) }}</span>
            @endforeach
        </div>
    @endif
@endif
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const quantity = document.getElementById('quantity');
    const container = document.getElementById('inventory-unit-fields');
    const initialRows = @json($serialRows);
    const syncRows = () => {
        if (quantity.value === '') return;
        const count = Math.max(0, Math.min(1000, Number.parseInt(quantity.value, 10) || 0));
        while (container.children.length < count) {
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'col-md-6';
            row.dataset.unitRow = '';
            const label = document.createElement('label');
            label.className = 'form-label';
            label.htmlFor = 'unit-serial-' + index;
            label.textContent = 'Unit ' + (index + 1) + ' — Serial Number';
            const id = document.createElement('input');
            id.type = 'hidden';
            id.name = 'units[' + index + '][id]';
            id.value = initialRows[index]?.id || '';
            const input = document.createElement('input');
            input.type = 'text';
            input.id = label.htmlFor;
            input.name = 'units[' + index + '][serial_number]';
            input.className = 'form-control';
            input.maxLength = 255;
            input.value = initialRows[index]?.serial_number || '';
            row.append(label, id, input);
            container.append(row);
        }
        Array.from(container.children).forEach((row, index) => {
            row.hidden = index >= count;
            row.querySelectorAll('input').forEach(input => input.disabled = row.hidden);
        });
    };
    quantity.addEventListener('input', syncRows);
    syncRows();
});
</script>
@endpush
