@extends('admin.menus.app')

@section('title', 'Crear Nuevo Menú')

@section('content')
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Crear Nuevo Menú</h3>
            <div class="mt-5">
                <form action="{{ route('admin.menus.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Título -->
                        <div class="sm:col-span-3">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                    required>
                            </div>
                        </div>

                        <!-- URL -->
                        <div class="sm:col-span-3">
                            <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                            <div class="mt-1">
                                <input type="text" name="url" id="url" placeholder="/ejemplo o https://..."
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>
                        </div>

                        <!-- Padre -->
                        <div class="sm:col-span-3">
                            <label for="parent_id" class="block text-sm font-medium text-gray-700">Padre</label>
                            <div class="mt-1">
                                <select id="parent_id" name="parent_id"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                    <option value="">-- Sin Padre (Nivel Superior) --</option>
                                    @foreach ($parents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->title }} ({{ $parent->location }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Orden -->
                        <div class="sm:col-span-3">
                            <label for="order" class="block text-sm font-medium text-gray-700">Orden</label>
                            <div class="mt-1">
                                <input type="number" name="order" id="order" value="0"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>
                        </div>

                        <!-- Ubicación -->
                        <div class="sm:col-span-3">
                            <label for="location" class="block text-sm font-medium text-gray-700">Ubicación</label>
                            <div class="mt-1">
                                <select id="location" name="location"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                    <option value="primary">Barra Principal (Header)</option>
                                    <option value="footer_1">Footer Columna 1 (FuerteJob)</option>
                                    <option value="footer_2">Footer Columna 2 (Empresas)</option>
                                    <option value="footer_3">Footer Columna 3 (Solicitantes)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Activo -->
                        <div class="sm:col-span-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" checked
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700">Activo</label>
                                    <p class="text-gray-500">Si está desactivado, no se mostrará en el portal.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5">
                        <div class="flex justify-end">
                            <a href="{{ route('admin.menus.index') }}"
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
