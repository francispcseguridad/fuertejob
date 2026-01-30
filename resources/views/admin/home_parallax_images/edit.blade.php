@extends('admin.menus.app')
@section('title', 'Editar Imagen Parallax')
@section('content')
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Editar Imagen Parallax</h3>
        <form action="{{ route('admin.home_parallax_images.update', $homeParallaxImage->id) }}" method="POST"
            class="mt-5 space-y-6">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Imagen (URL)</label>
                <input type="text" name="image" value="{{ $homeParallaxImage->image }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
            </div>
            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                    {{ $homeParallaxImage->is_active ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Activo</label>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('admin.home_parallax_images.index') }}"
                    class="mr-3 bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit"
                    class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">Actualizar</button>
            </div>
        </form>
    </div>
@endsection
