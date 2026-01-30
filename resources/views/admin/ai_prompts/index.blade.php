@extends('layouts.app')
@section('title', 'Gestión de Bonos del Portal')
@section('content')

    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-5 fw-bold text-dark">Gestión de Prompt IA</h1>
                <p class="text-muted lead">Define las reglas y conocimiento del Asistente de Fuertejob (System Instruction).
                </p>
            </div>
            <div>
                <!-- Actions or Date can go here -->
            </div>
        </div>

        <!-- Sección de Control y Compilación -->
        <div class="card shadow-sm border-0 bg-light mb-5">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-code-square me-2"></i>Prompt Compilado (Preview)
                    </h5>
                    <div class="d-flex align-items-center">
                        <span id="copy-message"
                            class="text-success small fw-bold me-2 opacity-0 transition-opacity">¡Copiado!</span>
                        <button onclick="copyPromptToClipboard()" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-clipboard me-1"></i> Copiar Prompt
                        </button>
                    </div>
                </div>

                <div class="position-relative">
                    <div id="compiled-prompt-output" class="form-control bg-white text-muted small border-0 shadow-sm"
                        style="height: 150px; overflow-y: auto; white-space: pre-wrap; font-family: monospace;">
                        Cargando prompt...
                    </div>
                </div>
            </div>
        </div>

        <!-- Base de Conocimiento -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-secondary mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Base de Conocimiento
            </h4>
            <button onclick="showAddCategoryModal()" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-folder-plus me-2"></i>Nueva Categoría
            </button>
        </div>

        <div id="knowledge-base-container" class="row g-4">
            <!-- El contenido se renderizará aquí -->
        </div>

        <!-- Empty State Template (Hidden) -->
        <div id="empty-state" class="d-none text-center py-5">
            <div class="text-muted opacity-50 mb-3">
                <i class="bi bi-inbox fs-1"></i>
            </div>
            <p class="text-muted">No hay conocimiento definido. ¡Añade una categoría para comenzar!</p>
        </div>

        <div class="mt-5 text-center">
            <button onclick="showAddItemModal(null)" class="btn btn-outline-dark rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i>Añadir Item Directo
            </button>
        </div>

    </div>

    <!-- Modal para Añadir/Editar Item -->
    <!-- Usamos estructura Bootstrap, pero controlado manualmente por JS por compatibilidad -->
    <div id="item-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modal-title">Editar Item</h5>
                    <button type="button" class="btn-close" onclick="closeModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal-category"
                            class="form-label text-muted small fw-bold text-uppercase">Categoría</label>
                        <select id="modal-category" class="form-select text-dark fw-bold">
                            <!-- Opciones -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal-title-input" class="form-label text-muted small fw-bold text-uppercase">Título
                            (Visible)</label>
                        <input type="text" id="modal-title-input" class="form-control" placeholder="Ej: Subir CV">
                    </div>

                    <div class="mb-3">
                        <label for="modal-detail-input"
                            class="form-label text-muted small fw-bold text-uppercase">Instrucción / Detalle</label>
                        <textarea id="modal-detail-input" rows="4" class="form-control"
                            placeholder="Instrucción precisa para el modelo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light text-muted" onclick="closeModal()">Cancelar</button>
                    <button type="button" onclick="saveItem()" id="save-item-button"
                        class="btn btn-primary px-4 rounded-pill">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Overlay manual para el modal si Bootstrap JS no está presente/conflictivo -->
    <div id="modal-backdrop" class="modal-backdrop fade show d-none"></div>

    <!-- Modal para Añadir Nueva Categoría -->
    <div id="category-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-body p-4 text-center">
                    <h5 class="fw-bold mb-3">Nueva Categoría</h5>
                    <input type="text" id="new-category-name" class="form-control mb-4 text-center"
                        placeholder="Nombre de Categoría">

                    <div class="d-grid gap-2">
                        <button onclick="addNewCategory()" class="btn btn-primary rounded-pill">Crear</button>
                        <button onclick="closeCategoryModal()"
                            class="btn btn-link text-decoration-none text-muted btn-sm">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // =========================================================================
        // === LOGIC (Bootstrap Adapted) ===
        // =========================================================================

        let fuertejobKnowledgeBase = @json($groupedPrompts);

        if (!fuertejobKnowledgeBase) {
            fuertejobKnowledgeBase = [];
        }

        let editingContext = {
            sectionIndex: null,
            itemIndex: null
        };

        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                document.querySelector('input[name="_token"]')?.value;
        }

        // --- RENDERIZADO ---

        function renderKnowledgeBase() {
            const container = document.getElementById('knowledge-base-container');
            container.innerHTML = '';

            if (fuertejobKnowledgeBase.length === 0) {
                document.getElementById('empty-state').classList.remove('d-none');
                return;
            } else {
                document.getElementById('empty-state').classList.add('d-none');
            }

            fuertejobKnowledgeBase.forEach((section, sIndex) => {
                // Column for Grid Layout
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-6';

                // Bootstrap Card
                const card = document.createElement('div');
                card.className = 'card h-100 shadow-sm border-0';

                // Header
                const header = document.createElement('div');
                header.className =
                    'card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center';
                header.innerHTML = `
                    <h5 class="fw-bold text-dark mb-0">${section.category}</h5>
                    <div class="dropdown">
                        <!-- Botones de Acción de Categoría -->
                        <button onclick="showAddItemModal(${sIndex})" class="btn btn-link text-success p-1" title="Añadir Item">
                            <i class="bi bi-plus-circle-fill fs-5"></i>
                        </button>
                        <button onclick="deleteSection(${sIndex})" class="btn btn-link text-danger p-1" title="Eliminar Categoría">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                    </div>
                `;
                card.appendChild(header);

                // Body & List
                const body = document.createElement('div');
                body.className = 'card-body px-0 pb-2'; // Padding controlled in items

                const list = document.createElement('div');
                list.className = 'list-group list-group-flush';

                if (!section.items || section.items.length === 0) {
                    list.innerHTML = '<div class="text-center p-3 text-muted small fst-italic">Sin items</div>';
                } else {
                    section.items.forEach((item, iIndex) => {
                        const listItem = document.createElement('div');
                        listItem.className =
                            'list-group-item px-4 py-3 border-0 border-bottom d-flex justify-content-between align-items-start list-group-item-action';
                        listItem.style.cursor = 'pointer';
                        listItem.onclick = () => showEditItemModal(sIndex, iIndex);

                        listItem.innerHTML = `
                            <div class="me-3">
                                <div class="fw-bold text-dark mb-1">${item.title}</div>
                                <small class="text-muted d-block" style="line-height:1.4;">${item.detail}</small>
                            </div>
                            <button onclick="event.stopPropagation(); deleteItem(${sIndex}, ${iIndex})" 
                                class="btn btn-sm btn-light text-danger border-0 rounded-circle" title="Eliminar">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `;
                        list.appendChild(listItem);
                    });
                }

                body.appendChild(list);
                card.appendChild(body);
                col.appendChild(card);
                container.appendChild(col);
            });

            updateCompiledPrompt();
        }

        // --- CRUD (Manteniendo lógica anterior) ---

        async function deleteSection(sIndex) {
            const section = fuertejobKnowledgeBase[sIndex];
            if (confirm(`¿Eliminar categoría "${section.category}" y todo su contenido?`)) {

                const promises = [];
                if (section.items && section.items.length > 0) {
                    section.items.forEach(item => {
                        if (item.id) {
                            promises.push(fetch(`/admin/ai_prompts/${item.id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }));
                        }
                    });
                }

                try {
                    await Promise.all(promises);
                    fuertejobKnowledgeBase.splice(sIndex, 1);
                    renderKnowledgeBase();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error parcial al eliminar.');
                }
            }
        }

        function deleteItem(sIndex, iIndex) {
            const item = fuertejobKnowledgeBase[sIndex].items[iIndex];
            if (confirm(`¿Eliminar item "${item.title}"?`)) {
                if (item.id) {
                    fetch(`/admin/ai_prompts/${item.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                fuertejobKnowledgeBase[sIndex].items.splice(iIndex, 1);
                                renderKnowledgeBase();
                            } else {
                                alert('Error: ' + d.message);
                            }
                        })
                        .catch(e => {
                            console.error(e);
                            alert('Error de conexión.');
                        });
                } else {
                    fuertejobKnowledgeBase[sIndex].items.splice(iIndex, 1);
                    renderKnowledgeBase();
                }
            }
        }

        function saveItem() {
            const selectedCategory = document.getElementById('modal-category').value;
            const title = document.getElementById('modal-title-input').value.trim();
            const detail = document.getElementById('modal-detail-input').value.trim();
            const saveBtn = document.getElementById('save-item-button');

            if (!title || !detail) {
                alert("Completa todos los campos.");
                return;
            }

            saveBtn.disabled = true;
            saveBtn.textContent = 'Guardando...';

            let url = '/admin/ai_prompts';
            let method = 'POST';
            let isEdit = (editingContext.itemIndex !== null);
            let currentItem = isEdit ? fuertejobKnowledgeBase[editingContext.sectionIndex].items[editingContext.itemIndex] :
                null;

            if (isEdit && currentItem && currentItem.id) {
                url += `/${currentItem.id}`;
                method = 'PUT';
            }

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category: selectedCategory,
                        title,
                        detail,
                        status: 'active'
                    })
                })
                .then(r => {
                    if (!r.ok) throw new Error('Server Error');
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        const savedItem = data.data;
                        if (isEdit) {
                            const oldSectionIndex = editingContext.sectionIndex;
                            const oldItemIndex = editingContext.itemIndex;
                            if (fuertejobKnowledgeBase[oldSectionIndex].category !== selectedCategory) {
                                fuertejobKnowledgeBase[oldSectionIndex].items.splice(oldItemIndex, 1);
                                let newSection = fuertejobKnowledgeBase.find(s => s.category === selectedCategory);
                                if (!newSection) {
                                    newSection = {
                                        category: selectedCategory,
                                        items: []
                                    };
                                    fuertejobKnowledgeBase.push(newSection);
                                }
                                newSection.items.push(savedItem);
                            } else {
                                fuertejobKnowledgeBase[oldSectionIndex].items[oldItemIndex] = savedItem;
                            }
                        } else {
                            let section = fuertejobKnowledgeBase.find(s => s.category === selectedCategory);
                            if (!section) {
                                section = {
                                    category: selectedCategory,
                                    items: []
                                };
                                fuertejobKnowledgeBase.push(section);
                            }
                            section.items.push(savedItem);
                        }
                        renderKnowledgeBase();
                        closeModal();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(e => {
                    console.error(e);
                    alert('Error al guardar.');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Guardar Cambios';
                });
        }

        function addNewCategory() {
            const categoryName = document.getElementById('new-category-name').value.trim();
            if (!categoryName) return;
            if (fuertejobKnowledgeBase.some(s => s.category.toLowerCase() === categoryName.toLowerCase())) {
                alert("Ya existe.");
                return;
            }
            fuertejobKnowledgeBase.push({
                category: categoryName,
                items: []
            });
            renderKnowledgeBase();
            closeCategoryModal();
        }

        // --- MODAL HELPERS (Bootstrap imitation) ---

        function toggleModal(modalId, show) {
            const modal = document.getElementById(modalId);
            const backdrop = document.getElementById('modal-backdrop');

            if (show) {
                modal.style.display = 'block';
                modal.classList.add('show');
                backdrop.classList.remove('d-none');
            } else {
                modal.style.display = 'none';
                modal.classList.remove('show');

                // Only hide backdrop if no other modals are open (simple check)
                if (modalId === 'item-modal' || modalId === 'category-modal') {
                    backdrop.classList.add('d-none');
                }
            }
        }

        function showEditItemModal(sIndex, iIndex) {
            const item = fuertejobKnowledgeBase[sIndex].items[iIndex];
            editingContext = {
                sectionIndex: sIndex,
                itemIndex: iIndex
            };

            document.getElementById('modal-title').textContent = 'Editar Item';
            document.getElementById('modal-title-input').value = item.title;
            document.getElementById('modal-detail-input').value = item.detail;
            loadCategoryOptions(fuertejobKnowledgeBase[sIndex].category, false);

            toggleModal('item-modal', true);
        }

        function showAddItemModal(sIndex = null) {
            editingContext = {
                sectionIndex: sIndex,
                itemIndex: null
            };

            document.getElementById('modal-title').textContent = 'Nuevo Item';
            document.getElementById('modal-title-input').value = '';
            document.getElementById('modal-detail-input').value = '';

            const initialCategory = sIndex !== null ? fuertejobKnowledgeBase[sIndex].category : null;
            loadCategoryOptions(initialCategory, false);

            toggleModal('item-modal', true);
        }

        function showAddCategoryModal() {
            document.getElementById('new-category-name').value = '';
            toggleModal('category-modal', true);
        }

        function closeModal() {
            toggleModal('item-modal', false);
        }

        function closeCategoryModal() {
            toggleModal('category-modal', false);
        }

        function loadCategoryOptions(selectedCategory = null, disableSelection = false) {
            const select = document.getElementById('modal-category');
            select.innerHTML = '';
            select.disabled = disableSelection;

            const addOption = document.createElement('option');
            addOption.value = 'ADD_NEW';
            addOption.textContent = '+ Nueva Categoría...';
            select.appendChild(addOption);

            fuertejobKnowledgeBase.forEach(section => {
                const option = document.createElement('option');
                option.value = section.category;
                option.textContent = section.category;
                if (section.category === selectedCategory) option.selected = true;
                select.appendChild(option);
            });

            select.onchange = function() {
                if (this.value === 'ADD_NEW') {
                    if (selectedCategory) this.value = selectedCategory;
                    showAddCategoryModal();
                }
            };
        }

        // --- PROMPT ---
        const baseSystemPrompt = `
            Actúa como el Asistente Oficial de la plataforma de empleo **Fuertejob**.
            Tu objetivo principal es ayudar a los usuarios (Candidatos y Empresas) a navegar y utilizar las funcionalidades del portal, además de ofrecer consejos laborales generales y actualizados.

            **REGLAS Y CONOCIMIENTO BASE:**
            * Responde siempre en español.
            * Prioriza las respuestas basadas en la siguiente sección de CONOCIMIENTO ESPECÍFICO DE FUERTEJOB.
            * NO reveles detalles técnicos internos (nombres de rutas, nombres de tablas, funciones de código, etc.). Usa solo los nombres visibles en la interfaz.
            * Si la pregunta es genérica (ej: "¿Cómo negociar mi sueldo?"), usa tu conocimiento general y la herramienta de búsqueda de Google.
            * Sé profesional, amable y conciso.

            **CONOCIMIENTO ESPECÍFICO DE FUERTEJOB (Gestionable):**
        `;

        function updateCompiledPrompt() {
            let prompt = baseSystemPrompt;
            let counter = 1;

            fuertejobKnowledgeBase.forEach(section => {
                if (section.items && section.items.length > 0) {
                    prompt += `\n\n${counter}. **${section.category}:**\n`;
                    section.items.forEach(item => {
                        prompt += `* **${item.title}:** ${item.detail}\n`;
                    });
                    counter++;
                }
            });

            document.getElementById('compiled-prompt-output').textContent = prompt.trim();
        }

        function copyPromptToClipboard() {
            const promptText = document.getElementById('compiled-prompt-output').textContent;
            navigator.clipboard.writeText(promptText).then(() => {
                const msg = document.getElementById('copy-message');
                msg.classList.remove('opacity-0');
                setTimeout(() => msg.classList.add('opacity-0'), 2000);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderKnowledgeBase();
        });
    </script>
@endsection
