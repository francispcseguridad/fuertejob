@php
    $isEdit = $location && $location->exists;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="city" class="form-label">Ciudad / Municipio *</label>
        <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror"
            value="{{ old('city', $location->city) }}" required>
        @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="island" class="form-label">Isla</label>
        <input type="text" name="island" id="island" class="form-control @error('island') is-invalid @enderror"
            value="{{ old('island', $location->island) }}">
        @error('island')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="province" class="form-label">Provincia</label>
        <input type="text" name="province" id="province"
            class="form-control @error('province') is-invalid @enderror"
            value="{{ old('province', $location->province) }}">
        @error('province')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <input type="hidden" name="country" value="{{ old('country', $location->country ?? 'España') }}">

    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.localidades.index') }}" class="btn btn-link text-decoration-none">Volver al
            listado</a>
        <button type="submit" class="btn btn-primary">
            {{ $isEdit ? 'Actualizar' : 'Crear' }}
        </button>
    </div>
</form>
