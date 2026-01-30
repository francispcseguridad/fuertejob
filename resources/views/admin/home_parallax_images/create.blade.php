@extends('layout.app')
@section('title', 'Crear Imagen Parallax')
@section('content')
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900">Crear Imagen Parallax</h3>
        <form action="{{ route('admin.home_parallax_images.store') }}" method="POST" class="mt-5 space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Imagen (URL)</label>
                <input type="text" name="image"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                <p class="mt-1 text-xs text-gray-500">Copia y pega la URL de la imagen aquí.</p>
            </div>
            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1" checked
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Activo</label>
            </div>
            <div class="flex justify-end">
                <a href="{{ route('admin.home_parallax_images.index') }}"
                    class="mr-3 bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit"
                    class="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
@endsection
class="btn btn-light border">Cancelar</a>
<button type="submit" class="btn btn-primary">
    <i class="bi bi-save me-1"></i> Guardar
</button>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
@endsection
