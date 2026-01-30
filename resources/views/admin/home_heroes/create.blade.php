@extends('layouts.app')

@section('title', 'Crear Banner')

@section('content')
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Crear Nuevo Banner</h3>
            <div class="mt-5">
                <form action="{{ route('admin.home_heroes.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <!-- Título -->
                        <div class="sm:col-span-6">
                            <label for="title" class="block text-sm font-medium text-gray-700">Título Principal</label>
                            <div class="mt-1">
                                <input type="text" name="title" id="title"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                    required>
                            </div>
                        </div>

                        <!-- Subtítulo -->
                        <div class="sm:col-span-6">
                            <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtítulo</label>
                            <div class="mt-1">
                                <input type="text" name="subtitle" id="subtitle"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>
                        </div>

                        <!-- Botón 1 -->
                        <div class="sm:col-span-3">
                            <label for="button1_text" class="block text-sm font-medium text-gray-700">Texto Botón 1</label>
                            <input type="text" name="button1_text" id="button1_text" placeholder="Ej: BUSCAS EMPLEO"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        <div class="sm:col-span-3">
                            <label for="button1_url" class="block text-sm font-medium text-gray-700">URL Botón 1</label>
                            <input type="text" name="button1_url" id="button1_url" placeholder="#"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>

                        <!-- Botón 2 -->
                        <div class="sm:col-span-3">
                            <label for="button2_text" class="block text-sm font-medium text-gray-700">Texto Botón 2</label>
                            <input type="text" name="button2_text" id="button2_text" placeholder="Ej: OFRECES EMPLEO"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>
                        <div class="sm:col-span-3">
                            <label for="button2_url" class="block text-sm font-medium text-gray-700">URL Botón 2</label>
                            <input type="text" name="button2_url" id="button2_url" placeholder="#"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        </div>

                        <!-- Imagen de Fondo (URL por ahora) -->
                        <div class="sm:col-span-6">
                            <label for="background_image" class="block text-sm font-medium text-gray-700">URL Imagen
                                Fondo</label>
                            <input type="text" name="background_image" id="background_image"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-5">
                        <div class="flex justify-end">
                            <a href="{{ route('admin.home_heroes.index') }}"
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
