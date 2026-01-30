<div class="mb-3">
    <label for="institution" class="form-label">Institución Educativa</label>
    <input id="institution" type="text" class="form-control @error('institution') is-invalid @enderror"
        name="institution" value="{{ old('institution', $education->institution) }}" required>
    @error('institution')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <label for="degree" class="form-label">Título / Grado Obtenido</label>
    <input id="degree" type="text" class="form-control @error('degree') is-invalid @enderror" name="degree"
        value="{{ old('degree', $education->degree) }}" required>
    @error('degree')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <label for="field_of_study" class="form-label">Campo de Estudio (Ej: Ingeniería Informática)</label>
    <input id="field_of_study" type="text" class="form-control @error('field_of_study') is-invalid @enderror"
        name="field_of_study" value="{{ old('field_of_study', $education->field_of_study) }}">
    @error('field_of_study')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_date" class="form-label">Fecha de Inicio</label>
        <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror"
            name="start_date" value="{{ old('start_date', optional($education->start_date)->format('Y-m-d')) }}"
            required>
        @error('start_date')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">Fecha de Finalización (si aplica)</label>
        <input id="end_date_input" type="date" class="form-control @error('end_date') is-invalid @enderror"
            name="end_date" value="{{ old('end_date', optional($education->end_date)->format('Y-m-d')) }}"
            {{ $education->is_current ? 'disabled' : '' }}>
        @error('end_date')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current_checkbox"
        {{ old('is_current', $education->is_current) ? 'checked' : '' }}
        onchange="document.getElementById('end_date_input').disabled = this.checked;">
    <label class="form-check-label" for="is_current_checkbox">
        Actualmente estoy estudiando aquí
    </label>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Notas o Logros Adicionales</label>
    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"
        rows="4">{{ old('description', $education->description) }}</textarea>
    @error('description')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>
