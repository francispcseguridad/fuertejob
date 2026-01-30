<div class="repeater-item" data-index="{{ $index }}">
    <div class="btn-remove" onclick="removeRow(this)"><i class="bi bi-x-lg"></i></div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Título o Estudio</label>
            <input type="text" name="education[{{ $index }}][degree]" class="form-control form-control-lg fs-6"
                value="{{ $edu->degree }}" required placeholder="Ej: Grado en Turismo">
        </div>
        <div class="col-md-6">
            <label class="form-label">Centro Educativo</label>
            <input type="text" name="education[{{ $index }}][institution]"
                class="form-control form-control-lg fs-6" value="{{ $edu->institution }}" required
                placeholder="Ej: IES Puerto del Rosario">
        </div>
        <div class="col-md-4">
            <label class="form-label">Inicio</label>
            <input type="date" name="education[{{ $index }}][start_date]" class="form-control"
                value="{{ $edu->getAttributes()['start_date'] ? \Carbon\Carbon::parse($edu->getAttributes()['start_date'])->format('Y-m-d') : '' }}"
                required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fin</label>
            <input type="date" name="education[{{ $index }}][end_date]"
                class="form-control end-date-input {{ $edu->getAttributes()['end_date'] ? '' : 'bg-light opacity-50' }}"
                value="{{ $edu->getAttributes()['end_date'] ? \Carbon\Carbon::parse($edu->getAttributes()['end_date'])->format('Y-m-d') : '' }}"
                {{ $edu->getAttributes()['end_date'] ? '' : 'disabled' }}>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="education[{{ $index }}][is_current]"
                    value="1" onchange="toggleCurrent(this)"
                    {{ $edu->getAttributes()['end_date'] ? '' : 'checked' }}>
                <label class="form-check-label small fw-bold text-success">En curso</label>
            </div>
        </div>
    </div>
</div>
