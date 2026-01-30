<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Support\Str;

class UserController extends Controller
{
    /**
     * Crea un usuario específico (info@pcseguridad.es) y devuelve un mensaje.
     */
    public function createSpecificUser()
    {
        // 1. Verificar si el usuario ya existe
        $email = 'info@pcseguridad.es';

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => "El usuario con el email {$email} ya existe."
            ], 409); // Código 409 Conflict
        }

        // 2. Crear el usuario
        // Generamos una contraseña segura aleatoria ya que no la estamos pidiendo por un formulario.
        // La longitud por defecto de Str::random() es 16.
        $password = 'Pescadilla#1234*';

        $user = User::create([
            'name'     => 'Info Pcseguridad', // Nombre a elección
            'email'    => $email,
            'password' => Hash::make($password), // Cifrar la contraseña
        ]);

        // 3. Devolver una respuesta
        // NO se recomienda devolver la contraseña en un entorno real.
        // Aquí se incluye solo para fines de demostración o prueba en un nivel.
        return response()->json([
            'message'  => "Usuario {$user->email} creado exitosamente.",
            'user_id'  => $user->id,
            'temp_password' => $password,
        ], 201); // Código 201 Created
    }
}