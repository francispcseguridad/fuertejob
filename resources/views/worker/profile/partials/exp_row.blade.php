<div class="repeater-item" data-index="{{ $index }}">
    <div class="btn-remove" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Puesto de trabajo</label>
            <input type="text" name="experiences[{{ $index }}][job_title]"
                class="form-control form-control-lg fs-6" value="{{ $exp->job_title }}" required
                placeholder="Ej: Recepcionista">
        </div>
        <div class="col-md-6">
            <label class="form-label">Empresa / Negocio</label>
            <input type="text" name="experiences[{{ $index }}][company_name]"
                class="form-control form-control-lg fs-6" value="{{ $exp->company_name }}" required
                placeholder="Ej: Hotel Fuerteventura">
        </div>
        <div class="col-md-4">
            <label class="form-label">Desde</label>
            <input type="date" name="experiences[{{ $index }}][start_date]" class="form-control"
                value="{{ $exp->getAttributes()['start_date'] ? \Carbon\Carbon::parse($exp->getAttributes()['start_date'])->format('Y-m-d') : '' }}"
                required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Hasta</label>
            <input type="date" name="experiences[{{ $index }}][end_date]"
                class="form-control end-date-input {{ $exp->getAttributes()['end_date'] ? '' : 'bg-light opacity-50' }}"
                value="{{ $exp->getAttributes()['end_date'] ? \Carbon\Carbon::parse($exp->getAttributes()['end_date'])->format('Y-m-d') : '' }}"
                {{ $exp->getAttributes()['end_date'] ? '' : 'disabled' }}>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="experiences[{{ $index }}][is_current]"
                    value="1" onchange="toggleCurrent(this)"
                    {{ $exp->getAttributes()['end_date'] ? '' : 'checked' }}>
                <label class="form-check-label small fw-bold text-primary">Trabajo actual</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">¿Qué tareas realizabas?</label>
            <textarea name="experiences[{{ $index }}][description]" class="form-control" rows="3"
                placeholder="Describe tus responsabilidades principales...">{{ $exp->description }}</textarea>
        </div>
    </div>
</div>
