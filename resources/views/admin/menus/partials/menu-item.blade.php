@php
    $menuPayload = [
        'id' => $menu->id,
        'title' => $menu->title,
        'url' => $menu->url,
        'order' => $menu->order,
        'location' => $menu->location,
        'is_active' => (bool) $menu->is_active,
        'parent_id' => $menu->parent_id,
        'parent_title' => optional($menu->parent)->title,
        'children' => $menu->children
            ->map(function ($child) {
                return [
                    'id' => $child->id,
                    'title' => $child->title,
                    'order' => $child->order,
                    'is_active' => (bool) $child->is_active,
                ];
            })
            ->values(),
]; @endphp
<div class="position-relative menu-item-container" style="padding-left: {{ $level * 3 }}rem;">
    {{-- Vertical Guide Line for nested items --}}
    @if ($level > 0)
        <div class="position-absolute start-0 top-0 bottom-0 border-start border-2 border-primary border-opacity-25"
            style="left: calc({{ ($level - 1) * 3 }}rem + 1.5rem); margin-top: -10px; margin-bottom: 10px;"></div>

        {{-- Curved connector --}}
        <div class="position-absolute border-bottom border-start border-2 border-primary border-opacity-25 rounded-bottom-4"
            style="width: 1.5rem; height: 1.5rem; left: calc({{ ($level - 1) * 3 }}rem + 1.5rem); top: 0;"></div>
    @endif

    <div class="card border-0 shadow-sm mb-3 rounded-4 overflow-hidden hover-lift">
        <div class="card-body p-4 position-relative z-1 bg-white">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-4">
                {{-- Icon & Info --}}
                <div class="d-flex align-items-center gap-4 flex-grow-1">
                    <div class="flex-shrink-0">
                        <div class="d-flex align-items-center justify-content-center bg-{{ $level == 0 ? 'primary' : 'info' }} bg-opacity-10 text-{{ $level == 0 ? 'primary' : 'info' }} rounded-circle"
                            style="width: 56px; height: 56px;">
                            <i class="bi {{ $level == 0 ? 'bi-diagram-2-fill' : 'bi-arrow-return-right' }} fs-4"></i>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold text-dark mb-0">{{ $menu->title }}</h5>
                            @if (!$menu->is_active)
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1"
                                    style="font-size: 0.65rem;">
                                    <i class="bi bi-eye-slash-fill me-1"></i>Oculto
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted" style="font-size: 0.85rem;">
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-link-45deg text-primary opacity-50 fs-5"></i>
                                <span class="text-truncate" style="max-width: 200px;">
                                    {{ $menu->url ?: 'Sin enlace directo' }}
                                </span>
                            </div>
                            <span class="text-black-50 opacity-25">|</span>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-layout-sidebar text-primary opacity-50"></i>
                                <span>{{ ucfirst(str_replace('_', ' ', $menu->location)) }}</span>
                            </div>
                            <span class="text-black-50 opacity-25">|</span>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-sort-numeric-down text-primary opacity-50"></i>
                                <span>Orden: {{ $menu->order ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div
                    class="d-flex align-items-center gap-2 pt-3 pt-lg-0 border-top border-lg-0 border-light-subtle w-100 w-lg-auto justify-content-end">
                    <button type="button" class="btn btn-light text-secondary btn-icon rounded-circle hover-primary"
                        data-view-trigger data-menu='@json($menuPayload)' data-bs-toggle="tooltip"
                        title="Ver detalles">
                        <i class="bi bi-eye"></i>
                    </button>

                    <button type="button" class="btn btn-light text-primary btn-icon rounded-circle hover-primary"
                        data-edit-trigger data-menu='@json($menuPayload)'
                        data-action="{{ route('admin.menus.update', $menu->id) }}" data-bs-toggle="tooltip"
                        title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </button>

                    <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST"
                        onsubmit="return confirm('¿Confirma eliminar este menú? Esta acción no se puede deshacer.');"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-light text-danger btn-icon rounded-circle hover-danger"
                            data-bs-toggle="tooltip" title="Eliminar">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($menu->children->count())
    <div class="menu-children-container">
        @foreach ($menu->children as $child)
            @include('admin.menus.partials.menu-item', ['menu' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
