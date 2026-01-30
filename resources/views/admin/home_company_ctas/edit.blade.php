@extends('layout.app')
@section('title', 'Editar CTA Empresa')
@section('content')
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Editar CTA Empresa</h3>
        <form action="{{ route('admin.home_company_ctas.update', $homeCompanyCta->id) }}" method="POST"
            class="mt-5 space-y-6">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Título</label>
                <input type="text" name="title" value="{{ $homeCompanyCta->title }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"
                    required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Texto Botón</label>
                <input type="text" name="button_text" value="{{ $homeCompanyCta->button_text }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">URL Botón</label>
                <input type="text" name="button_url" value="{{ $homeCompanyCta->button_url }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Imagen Fondo (URL)</label>
                <input type="text" name="background_image" value="{{ $homeCompanyCta->background_image }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </div>
            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                    {{ $homeCompanyCta->is_active ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Activo</label>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('admin.home_company_ctas.index') }}"
                    class="mr-3 bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit"
                    class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
