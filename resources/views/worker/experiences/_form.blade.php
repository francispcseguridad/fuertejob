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
        <label for="start_date" class="form-label">Fecha de Inicio</label>
        <input id="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror"
            name="start_date" value="{{ old('start_date', optional($experience->start_date)->format('Y-m-d')) }}"
            required>
        @error('start_date')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="end_date" class="form-label">Fecha de Fin (si aplica)</label>
        <input id="end_date_input" type="date" class="form-control @error('end_date') is-invalid @enderror"
            name="end_date" value="{{ old('end_date', optional($experience->end_date)->format('Y-m-d')) }}"
            {{ $experience->is_current ? 'disabled' : '' }}>
        @error('end_date')
            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current_checkbox"
        {{ old('is_current', $experience->is_current) ? 'checked' : '' }}
        onchange="document.getElementById('end_date_input').disabled = this.checked;">
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
