@extends('layouts.app')

@section('title', 'Editar Contenido CMS/Blog')

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        #body-editor .ql-editor {
            min-height: 520px;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .form-card {
            border-radius: 1.5rem;
        }
    </style>
@endsection

@section('content')
    @php
        $initialType = old('type', $cmsContent->type);
        $showMenuSection = $initialType === \App\Models\CmsContent::TYPE_PAGE;
        $menuInfoBaseUrl = $infoBaseUrl ?? 'https://www.fuertejob.com/info/';
        $menuEnabledValue = old('menu_enabled', isset($linkedMenu) ? 1 : 0);
    @endphp
    <div class="container py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <p class="small text-uppercase text-muted fw-semibold mb-1">Portada & Blog</p>
                <h1 class="h3 fw-bold text-dark mb-0">Editar contenido</h1>
                <p class="text-muted mb-0">Actualiza el contenido seleccionado y controla su visibilidad en el portal.</p>
            </div>
            <a href="{{ route('admin.cms_contents.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-2"></i>Volver al listado
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-2"></i>
                    <h5 class="alert-heading mb-0 fw-bold">Hay errores en el formulario</h5>
                </div>
                <p class="mb-0">Por favor, revisa los campos marcados en rojo.</p>
                <hr>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.cms_contents.update', ['contenido' => $cmsContent->id]) }}"
            enctype="multipart/form-data" id="cms-content-form">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm form-card mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="mb-4">
                                <label for="title" class="form-label fw-semibold">Título principal</label>
                                <input type="text" name="title" id="title"
                                    value="{{ old('title', $cmsContent->title) }}" required
                                    class="form-control form-control-lg @error('title') is-invalid @enderror">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="slug" class="form-label fw-semibold">Slug (URL amigable)</label>
                                <input type="text" name="slug" id="slug"
                                    value="{{ old('slug', $cmsContent->slug) }}"
                                    class="form-control form-control-lg @error('slug') is-invalid @enderror"
                                    placeholder="ejemplo-de-slug">
                                <small class="text-muted">Se utiliza para construir la URL pública.</small>
                                @error('slug')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Contenido (HTML/Markdown)</label>
                                <textarea name="body" id="body" class="d-none">{{ old('body', $cmsContent->body) }}</textarea>
                                <div id="body-editor" class="border rounded-4"></div>
                                @error('body')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm form-card">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Imagen destacada</h5>
                                    <p class="text-muted mb-0">Renueva la imagen manteniendo la proporción recomendada 2:1.
                                    </p>
                                </div>
                                <span class="badge text-bg-primary bg-opacity-10 text-primary fw-semibold rounded-pill">
                                    2:1 Ratio
                                </span>
                            </div>

                            @if ($cmsContent->imagen_url)
                                <div class="mb-3">
                                    <p class="text-muted small mb-2">Imagen actual</p>
                                    <img src="{{ asset($cmsContent->imagen_url) }}" alt="Imagen actual"
                                        class="img-fluid rounded-4 border shadow-sm">
                                </div>
                            @endif

                            <input type="hidden" name="cropped_image" id="cropped_image">
                            <div class="mb-3">
                                <label for="image_upload" class="form-label">Subir nueva imagen</label>
                                <input type="file" name="image_upload" id="image_upload" accept="image/*"
                                    class="form-control @error('image_upload') is-invalid @enderror">
                                @error('image_upload')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Formatos permitidos: JPG, PNG, WebP (máx. 4MB).</small>
                            </div>

                            <div id="cropContainer" class="d-none rounded-4 overflow-hidden shadow-sm bg-light mb-3">
                                <img id="imageToCrop" src="" class="img-fluid w-100" alt="Vista previa">
                            </div>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1 text-primary"></i>Si no cargas una nueva imagen, se
                                conservará la actual.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm form-card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Opciones de publicación</h5>
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo de contenido</label>
                                <select name="type" id="type"
                                    class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach ($types as $key => $name)
                                        <option value="{{ $key }}" @selected(old('type', $cmsContent->type) == $key)>
                                            {{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_published"
                                    name="is_published" value="1" @checked(old('is_published', $cmsContent->is_published))>
                                <label class="form-check-label" for="is_published">Contenido visible</label>
                            </div>

                            <div class="mb-3">
                                <label for="published_at" class="form-label">Fecha de publicación</label>
                                <input type="date" name="published_at" id="published_at"
                                    value="{{ old('published_at', optional($cmsContent->published_at)->format('Y-m-d')) }}"
                                    class="form-control @error('published_at') is-invalid @enderror">
                                @error('published_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <input type="hidden" name="user_id"
                                value="{{ old('user_id', $cmsContent->user_id ?? $currentUserId) }}">
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm form-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">SEO y Metadatos</h5>
                            <div class="mb-3">
                                <label for="meta_title" class="form-label">Título SEO</label>
                                <input type="text" name="meta_title" id="meta_title" maxlength="255"
                                    value="{{ old('meta_title', $cmsContent->meta_title) }}"
                                    class="form-control @error('meta_title') is-invalid @enderror"
                                    placeholder="Máx. 60 caracteres">
                                @error('meta_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="meta_description" class="form-label">Meta descripción</label>
                                <textarea name="meta_description" id="meta_description" rows="3" maxlength="500"
                                    class="form-control @error('meta_description') is-invalid @enderror" placeholder="Breve resumen para buscadores">{{ old('meta_description', $cmsContent->meta_description) }}</textarea>
                                @error('meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="meta_keywords" class="form-label">Palabras clave</label>
                                <input type="text" name="meta_keywords" id="meta_keywords" maxlength="255"
                                    value="{{ old('meta_keywords', $cmsContent->meta_keywords) }}"
                                    class="form-control @error('meta_keywords') is-invalid @enderror"
                                    placeholder="palabra1, palabra2, ...">
                                @error('meta_keywords')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div id="menu-section"
                        class="card border-0 shadow-sm form-card mt-4 {{ $showMenuSection ? '' : 'd-none' }}"
                        data-base-url="{{ $menuInfoBaseUrl }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">Menú asociado</h5>
                                    <p class="text-muted mb-0">Gestiona el enlace público hacia esta página informativa.
                                    </p>
                                </div>
                                <span class="badge text-bg-info bg-opacity-10 text-info fw-semibold rounded-pill">Solo
                                    páginas</span>
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" role="switch" id="menu_enabled"
                                    name="menu_enabled" value="1" @checked($menuEnabledValue)>
                                <label class="form-check-label" for="menu_enabled">Crear/actualizar entrada de
                                    menú</label>
                            </div>

                            <div id="menu-fields" class="{{ $menuEnabledValue ? '' : 'd-none' }}">
                                <input type="hidden" name="menu_id"
                                    value="{{ old('menu_id', optional($linkedMenu)->id) }}">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">URL generada</label>
                                    <div class="form-control bg-light" id="menu_url_preview">
                                        {{ $menuInfoBaseUrl . ltrim(old('slug', $cmsContent->slug), '/') }}
                                    </div>
                                    <small class="text-muted">Se construye automáticamente con el slug del
                                        contenido.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="menu_title" class="form-label">Título del menú</label>
                                    <input type="text" name="menu_title" id="menu_title"
                                        class="form-control @error('menu_title') is-invalid @enderror"
                                        value="{{ old('menu_title', optional($linkedMenu)->title ?? old('title', $cmsContent->title)) }}"
                                        placeholder="Ej: Política de privacidad">
                                    <small class="text-muted">Si lo dejas vacío, se usará el título principal.</small>
                                    @error('menu_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="menu_location" class="form-label">Ubicación</label>
                                    <select name="menu_location" id="menu_location"
                                        class="form-select @error('menu_location') is-invalid @enderror">
                                        @foreach ($menuLocations as $value => $label)
                                            <option value="{{ $value }}" @selected(old('menu_location', optional($linkedMenu)->location ?? 'primary') == $value)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('menu_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="menu_parent_id" class="form-label">Menú padre (opcional)</label>
                                    <select name="menu_parent_id" id="menu_parent_id"
                                        class="form-select @error('menu_parent_id') is-invalid @enderror">
                                        <option value="">-- Sin padre (nivel principal) --</option>
                                        @foreach ($menuParents as $parent)
                                            <option value="{{ $parent->id }}" @selected(old('menu_parent_id', optional($linkedMenu)->parent_id) == $parent->id)>
                                                {{ $parent->title }} ({{ $parent->location }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('menu_parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="menu_order" class="form-label">Orden</label>
                                    <input type="number" name="menu_order" id="menu_order"
                                        class="form-control @error('menu_order') is-invalid @enderror"
                                        value="{{ old('menu_order', optional($linkedMenu)->order ?? 0) }}">
                                    @error('menu_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="menu_is_active"
                                        name="menu_is_active" value="1" @checked(old('menu_is_active', optional($linkedMenu)->is_active ?? 1))>
                                    <label class="form-check-label" for="menu_is_active">Mostrar en el portal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                    <i class="bi bi-save2 me-2"></i>Actualizar contenido
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quill = new Quill('#body-editor', {
                theme: 'snow',
                placeholder: 'Escribe o edita el contenido...',
                modules: {
                    toolbar: [
                        [{
                            header: [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }],
                        [{
                            indent: '-1'
                        }, {
                            indent: '+1'
                        }],
                        ['link', 'blockquote', 'code-block', 'image', 'video'],
                        ['clean']
                    ]
                }
            });

            const form = document.getElementById('cms-content-form');
            const hiddenBodyField = document.getElementById('body');
            const croppedImageInput = document.getElementById('cropped_image');
            const imageInput = document.getElementById('image_upload');
            const cropContainer = document.getElementById('cropContainer');
            const imageToCrop = document.getElementById('imageToCrop');
            let cropper;

            if (hiddenBodyField.value) {
                quill.root.innerHTML = hiddenBodyField.value;
            }

            form.addEventListener('submit', () => {
                hiddenBodyField.value = quill.root.innerHTML;
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 1000,
                        height: 500
                    });
                    if (canvas && croppedImageInput) {
                        croppedImageInput.value = canvas.toDataURL('image/jpeg');
                    }
                }
            });

            imageInput.addEventListener('change', (event) => {
                const [file] = event.target.files || [];
                if (!file) {
                    return;
                }
                const url = URL.createObjectURL(file);
                imageToCrop.src = url;
                cropContainer.classList.remove('d-none');

                if (cropper) {
                    cropper.destroy();
                }

                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 2,
                    viewMode: 1,
                    autoCropArea: 1
                });
            });

            const menuSection = document.getElementById('menu-section');
            if (menuSection) {
                const typeSelect = document.getElementById('type');
                const menuToggle = document.getElementById('menu_enabled');
                const menuFields = document.getElementById('menu-fields');
                const slugInput = document.getElementById('slug');
                const titleInput = document.getElementById('title');
                const menuUrlPreview = document.getElementById('menu_url_preview');
                const menuTitleInput = document.getElementById('menu_title');
                const baseUrl = menuSection.dataset.baseUrl;

                const slugify = (value) => value
                    .toString()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .replace(/-{2,}/g, '-');

                const updateMenuUrlPreview = () => {
                    const candidateSlug = slugInput.value.trim() || slugify(titleInput.value.trim());
                    const sanitizedSlug = candidateSlug ? candidateSlug.replace(/^\/+/, '') : '';
                    menuUrlPreview.textContent = sanitizedSlug ? `${baseUrl}${sanitizedSlug}` : baseUrl;
                    if (!menuTitleInput.value.trim() && titleInput.value.trim()) {
                        menuTitleInput.placeholder = titleInput.value.trim();
                    }
                };

                const toggleMenuFields = () => {
                    menuFields.classList.toggle('d-none', !menuToggle.checked);
                };

                const toggleMenuSection = () => {
                    const isPage = typeSelect.value === '{{ \App\Models\CmsContent::TYPE_PAGE }}';
                    if (!isPage) {
                        menuToggle.checked = false;
                        menuFields.classList.add('d-none');
                    }
                    menuSection.classList.toggle('d-none', !isPage);
                };

                typeSelect.addEventListener('change', () => {
                    toggleMenuSection();
                    updateMenuUrlPreview();
                });
                menuToggle.addEventListener('change', toggleMenuFields);
                slugInput.addEventListener('input', updateMenuUrlPreview);
                titleInput.addEventListener('input', updateMenuUrlPreview);

                toggleMenuSection();
                toggleMenuFields();
                updateMenuUrlPreview();
            }
        });
    </script>
@endsection
