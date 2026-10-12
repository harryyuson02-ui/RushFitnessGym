@php
    $selectedProvince = old('province');
    $selectedMunicipality = old('municipality');
    $selectedBarangay = old('barangay');
@endphp

<div class="grid gap-4 sm:col-span-2 sm:grid-cols-3"
     data-ph-location
     data-api-base="https://psgc.cloud/api/v2"
     data-selected-province="{{ $selectedProvince }}"
     data-selected-municipality="{{ $selectedMunicipality }}"
     data-selected-barangay="{{ $selectedBarangay }}">
    <label class="rush-label">
        Province
        <select class="rush-input" name="province" data-location-province required disabled>
            <option value="">Loading provinces…</option>
        </select>
    </label>
    <label class="rush-label">
        City / municipality
        <select class="rush-input" name="municipality" data-location-municipality required disabled>
            <option value="">Select a province first</option>
        </select>
    </label>
    <label class="rush-label">
        Barangay
        <select class="rush-input" name="barangay" data-location-barangay required disabled>
            <option value="">Select a city or municipality first</option>
        </select>
    </label>
    <input type="hidden" name="address" value="{{ old('address') }}" data-location-address>
    <p class="text-xs text-zinc-500 sm:col-span-3" data-location-status aria-live="polite">Choose your province, followed by your city or municipality, then your barangay.</p>
    <x-input-error :messages="$errors->get('province')" />
    <x-input-error :messages="$errors->get('municipality')" />
    <x-input-error :messages="$errors->get('barangay')" />
    <x-input-error :messages="$errors->get('address')" />
</div>
