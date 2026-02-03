<div class="mb-3">
    <label for="job_title" class="form-label">Título del Puesto</label>
    <input id="job_title" type="text" class="form-control @error('job_title') is-invalid @enderror" name="job_title"
        value="{{ old('job_title', $experience->job_title) }}" required>
    @error('job_title')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="mb-3">
    <label for="company_name" class="form-label">Empresa</label>
    <input id="company_name" type="text" class="form-control @error('company_name') is-invalid @enderror"
        name="company_name" value="{{ old('company_name', $experience->company_name) }}" required>
    @error('company_name')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="start_year" class="form-label">Año de Inicio</label>
        <input id="start_year" type="number" min="1900" max="2100"
            class="form-control @error('start_year') is-invalid @enderror" name="start_year"
            value="{{ old('start_year', $experience->start_year) }}" required>
        @error('start_year')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="end_year" class="form-label">Año de Fin (si aplica)</label>
        <input id="end_year_input" type="number" min="1900" max="2100"
            class="form-control @error('end_year') is-invalid @enderror" name="end_year"
            value="{{ old('end_year', $experience->end_year) }}" {{ $experience->is_current ? 'disabled' : '' }}>
        @error('end_year')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current_checkbox"
        {{ old('is_current', $experience->is_current) ? 'checked' : '' }}
        onchange="document.getElementById('end_year_input').disabled = this.checked;">
    <label class="form-check-label" for="is_current_checkbox">
        Actualmente trabajo aquí
    </label>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descripción de Responsabilidades</label>
    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description"
        rows="4">{{ old('description', $experience->description) }}</textarea>
    @error('description')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>
