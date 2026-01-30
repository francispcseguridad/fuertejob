<style>
    /* Estilos Premium para el Modal de Bonos */
    #bonoModal .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    #bonoModal .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #edf2f7;
        padding: 1.5rem 2rem;
    }

    #bonoModal .section-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #4a5568;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
    }

    #bonoModal .section-title i {
        margin-right: 8px;
        color: #4e73df;
    }

    #bonoModal .config-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
    }

    #bonoModal .form-label {
        font-weight: 600;
        color: #2d3748;
        font-size: 0.9rem;
    }

    #bonoModal .form-control,
    #bonoModal .form-select {
        border-radius: 10px;
        padding: 0.6rem 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }

    #bonoModal .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        background-color: #fff;
    }

    #bonoModal .card-preview {
        background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
        border-radius: 20px;
        color: white;
        padding: 2rem;
        position: sticky;
        top: 0;
    }

    .limit-icon {
        width: 35px;
        height: 35px;
        background: rgba(78, 115, 223, 0.1);
        color: #4e73df;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    /* Editor de Texto Enriquecido Mejorado */
    .description-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        background: #f8f9fa;
        border-radius: 12px 12px 0 0;
        padding: 0.6rem;
        border: 1px solid #e2e8f0;
        border-bottom: none;
    }

    .toolbar-group {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0 0.5rem;
        border-right: 1px solid #e2e8f0;
    }

    .toolbar-group:last-child {
        border-right: none;
    }

    .description-toolbar .btn-tool {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        border-radius: 6px;
        border: none;
        background: transparent;
        color: #4a5568;
        transition: all 0.2s;
    }

    .description-toolbar .btn-tool:hover {
        background: #e2e8f0;
        color: #4e73df;
    }

    .description-editor {
        min-height: 180px;
        max-height: 300px;
        overflow-y: auto;
        border-radius: 0 0 12px 12px;
        border: 1px solid #e2e8f0;
        padding: 1rem;
        font-size: 16px;
        background: #fff;
    }

    .description-editor.is-empty::before {
        content: attr(data-placeholder);
        color: #a0aec0;
        position: absolute;
        pointer-events: none;
    }
</style>

<div class="modal fade" id="bonoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="bonoModalLabel">Configuración de Bono</h5>
                    <p class="text-muted small mb-0">Define precios, límites y visibilidad para las empresas.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="bono-form" action="{{ route('admin.bonos.store') }}" method="POST">
                @csrf
                <input type="hidden" id="bono_method" name="_method" value="POST">
                <input type="hidden" id="bono_id" name="bono_id">

                <div class="modal-body p-4 p-lg-5">
                    <div class="row g-4">

                        {{-- Lado Izquierdo: Configuración --}}
                        <div class="col-lg-8">

                            {{-- Sección 1: Información Básica --}}
                            <div class="section-title"><i class="fas fa-info-circle"></i> Información General</div>
                            <div class="row g-3 mb-5">
                                <div class="col-md-7">
                                    <label class="form-label">Nombre del Plan</label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Ej: Plan Premium Trimestral" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Estado y Recomendación</label>
                                    <div class="d-flex gap-4 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active"
                                                id="is_active" value="1" checked>
                                            <label class="form-check-label small fw-bold" for="is_active">Activo</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="destacado"
                                                value="1" id="destacado_switch">
                                            <label class="form-check-label small fw-bold text-warning"
                                                for="destacado_switch">Recomendado</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Modelo de Analítica (nivel)</label>
                                    <select class="form-select" name="analytics_model_id" id="analytics_model_id">
                                        <option value="">Sin modelo</option>
                                        @foreach ($analyticsModels as $model)
                                            <option value="{{ $model->id }}">
                                                {{ $model->name }} @if ($model->level)
                                                    (Nivel {{ $model->level }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Define el nivel de estadísticas que ofrece este bono.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descripción comercial</label>
                                    <div class="description-toolbar">
                                        <div class="toolbar-group">
                                            <button type="button" class="btn-tool" data-command="bold"><i
                                                    class="bi bi-type-bold"></i></button>
                                            <button type="button" class="btn-tool" data-command="italic"><i
                                                    class="bi bi-type-italic"></i></button>
                                            <button type="button" class="btn-tool" data-command="underline"><i
                                                    class="bi bi-type-underline"></i></button>
                                        </div>
                                        <div class="toolbar-group">
                                            <button type="button" class="btn-tool"
                                                data-command="insertUnorderedList"><i
                                                    class="bi bi-list-ul"></i></button>
                                            <button type="button" class="btn-tool" data-command="insertOrderedList"><i
                                                    class="bi bi-list-ol"></i></button>
                                        </div>
                                        <div class="toolbar-group">
                                            <select id="description_font_family"
                                                class="form-select form-select-sm border-0 bg-transparent"
                                                style="width: 110px;">
                                                <option value="Arial, sans-serif">Arial</option>
                                                <option value="'Segoe UI', sans-serif">Segoe UI</option>
                                                <option value="Georgia, serif">Georgia</option>
                                            </select>
                                            <select id="description_font_size"
                                                class="form-select form-select-sm border-0 bg-transparent"
                                                style="width: 80px;">
                                                <option value="2">14px</option>
                                                <option value="3" selected>16px</option>
                                                <option value="4">18px</option>
                                                <option value="5">20px</option>
                                            </select>
                                        </div>
                                        <div class="toolbar-group">
                                            <button type="button" class="btn-tool" data-command="justifyLeft"><i
                                                    class="bi bi-text-left"></i></button>
                                            <button type="button" class="btn-tool" data-command="justifyCenter"><i
                                                    class="bi bi-text-center"></i></button>
                                        </div>
                                    </div>
                                    <div id="description_editor" class="description-editor is-empty"
                                        contenteditable="true"
                                        data-placeholder="Explica los beneficios de este bono..."></div>
                                    <textarea name="description" id="description_field" class="d-none">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            {{-- Sección 2: Precios y Duración --}}
                            <div class="section-title"><i class="fas fa-tags"></i> Precios y Vigencia</div>
                            <div class="row g-3 mb-5">
                                <div class="col-md-4">
                                    <label class="form-label">Precio Venta</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">€</span>
                                        <input type="number" step="0.01" name="price" class="form-control"
                                            placeholder="0.00" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Duración</label>
                                    <div class="input-group">
                                        <input type="number" name="duration_days" class="form-control"
                                            min="1" placeholder="30">
                                        <span class="input-group-text bg-white small">Días</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Producto</label>
                                    <div class="form-check form-switch d-flex align-items-center gap-2 mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_extra_toggle"
                                            value="1" id="is_extra_switch">
                                        <label class="form-check-label small mb-0 fw-bold text-primary"
                                            for="is_extra_switch">¿Es un servicio Extra?</label>
                                    </div>
                                    <input type="hidden" name="is_extra" id="is_extra_value" value="0">
                                </div>
                            </div>

                            {{-- Sección 3: Límites de Uso --}}
                            <div class="section-title"><i class="fas fa-sliders-h"></i> Recursos Incluidos</div>
                            <div class="config-box shadow-sm">
                                <div class="row g-4">
                                    <div class="col-md-3 text-center text-md-start">
                                        <label class="form-label d-block mb-1 small">Anuncios</label>
                                        <input type="number" name="offer_credits"
                                            class="form-control form-control-sm text-center text-md-start"
                                            min="0" value="0">
                                    </div>
                                    <div class="col-md-3 text-center text-md-start">
                                        <label class="form-label d-block mb-1 small">Visibilidad</label>
                                        <input type="number" name="visibility_days"
                                            class="form-control form-control-sm text-center text-md-start"
                                            min="0" value="0">
                                    </div>
                                    <div class="col-md-3 text-center text-md-start">
                                        <label class="form-label d-block mb-1 small">CV Descargas</label>
                                        <input type="number" name="cv_views"
                                            class="form-control form-control-sm text-center text-md-start"
                                            min="0" value="0">
                                    </div>
                                    <div class="col-md-3 text-center text-md-start">
                                        <label class="form-label d-block mb-1 small">Usuarios</label>
                                        <input type="number" name="user_seats"
                                            class="form-control form-control-sm text-center text-md-start"
                                            min="0" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Lado Derecho: Preview/Sidebar --}}
                        <div class="col-lg-4">
                            <div class="card-preview shadow-lg">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <span class="badge bg-primary rounded-pill px-3">Vista Previa</span>
                                    <i class="fas fa-crown text-warning"></i>
                                </div>
                                <h4 class="fw-bold mb-1">Resumen</h4>
                                <p class="small opacity-75">Configuración de créditos y costos.</p>

                                <hr class="my-4 opacity-25">

                                <div class="mb-4 text-center py-3">
                                    <span class="display-5 fw-bold" id="preview-price">0.00</span><span
                                        class="h4">€</span>
                                </div>

                                <div id="extra-badge-preview"
                                    class="d-none mt-4 p-3 rounded-4 bg-primary bg-opacity-25 border border-primary border-opacity-25 animate__animated animate__fadeIn">
                                    <div class="d-flex gap-2 align-items-center">
                                        <i class="fas fa-coins text-primary"></i>
                                        <div class="flex-grow-1">
                                            <strong class="d-block small">Coste en Créditos</strong>
                                            <input type="number" name="credit_cost"
                                                class="form-control form-control-sm bg-transparent text-white border-0 p-0 fw-bold"
                                                value="0"
                                                style="font-size: 1.2rem; outline: none !important; box-shadow: none;">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-flex align-items-center mb-3 small">
                                            <i class="fas fa-check-circle me-3 text-success"></i>
                                            <span>Consumo bajo demanda</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-3 small">
                                            <i class="fas fa-check-circle me-3 text-success"></i>
                                            <span>Activación inmediata</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light border-0 p-4">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                        data-bs-dismiss="modal">Descartar</button>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow">
                        <i class="fas fa-save me-2"></i> Guardar Bono
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos de precio y estado "Extra"
        const priceInput = document.querySelector('input[name="price"]');
        const pricePreview = document.getElementById('preview-price');
        const isExtraSwitch = document.getElementById('is_extra_switch');
        const extraBadge = document.getElementById('extra-badge-preview');
        const isExtraValueInput = document.getElementById('is_extra_value');

        // Elementos del Editor
        const descriptionEditor = document.getElementById('description_editor');
        const descriptionField = document.getElementById('description_field');
        const toolButtons = document.querySelectorAll('.btn-tool');
        const fontSelect = document.getElementById('description_font_family');
        const sizeSelect = document.getElementById('description_font_size');

        // Actualizar preview de precio
        priceInput.addEventListener('input', (e) => {
            pricePreview.innerText = e.target.value || '0.00';
        });

        // Sincronizar estado "Extra" (Mantiene funcionalidad original)
        const syncExtraState = checked => {
            if (extraBadge) {
                extraBadge.classList.toggle('d-none', !checked);
            }
            if (isExtraValueInput) {
                isExtraValueInput.value = checked ? '1' : '0';
            }
        };

        if (isExtraSwitch) {
            isExtraSwitch.addEventListener('change', (e) => syncExtraState(e.target.checked));
            syncExtraState(isExtraSwitch.checked);
        }

        // Lógica del Editor Rich Text
        const togglePlaceholder = () => {
            const isEmpty = !descriptionEditor.innerText.trim();
            descriptionEditor.classList.toggle('is-empty', isEmpty);
        };

        const updateField = () => {
            descriptionField.value = descriptionEditor.innerHTML;
        };

        descriptionEditor.addEventListener('input', () => {
            updateField();
            togglePlaceholder();
        });

        toolButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.execCommand(btn.dataset.command, false, null);
                descriptionEditor.focus();
                updateField();
            });
        });

        fontSelect.addEventListener('change', () => {
            document.execCommand('fontName', false, fontSelect.value);
            descriptionEditor.focus();
            updateField();
        });

        sizeSelect.addEventListener('change', () => {
            document.execCommand('fontSize', false, sizeSelect.value);
            descriptionEditor.focus();
            updateField();
        });

        // Funciones Globales para carga externa (Mantiene tus funciones window.)
        window.loadRichDescription = (content = '') => {
            descriptionEditor.innerHTML = content || '';
            descriptionField.value = content || '';
            togglePlaceholder();
        };

        window.resetRichDescription = () => {
            descriptionEditor.innerHTML = '';
            descriptionField.value = '';
            togglePlaceholder();
        };

        // Inicializar estado del editor si hay contenido previo
        if (descriptionField.value) {
            descriptionEditor.innerHTML = descriptionField.value;
            togglePlaceholder();
        }
    });
</script>
