<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VerificationController extends Controller
{
    use AuthorizesRequests, VerifiesEmails;

    /**
     * Donde redirigir a los usuarios después de la verificación.
     * * Dado que solo estamos verificando a los trabajadores, redirigimos a su dashboard.
     * Puedes ajustar esta ruta si tienes dashboards diferentes para otros roles.
     *
     * @var string
     */
    protected $redirectTo = '/candidatos/dashboard';

    /**
     * Crea una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct()
    {
        // El middleware 'auth' asegura que el usuario esté logueado para verificar.
        // El middleware 'throttle:6,1' limita los reenvíos a 6 por minuto.
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('resend');
    }

    /**
     * Marca el correo electrónico del usuario autenticado como verificado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        // Comprobar que el ID y el hash coincidan con el usuario
        if (! hash_equals((string) $request->route('id'), (string) $request->user()->getKey())) {
            return redirect($this->redirectPath())->with('error', 'El enlace de verificación no es válido.');
        }

        if (! hash_equals((string) $request->route('hash'), sha1($request->user()->getEmailForVerification()))) {
            return redirect($this->redirectPath())->with('error', 'El enlace de verificación no es válido.');
        }

        // Si el correo ya está verificado, redirigir
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath())->with('info', 'Tu correo ya estaba verificado.');
        }

        // Marcar como verificado y disparar el evento
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect($this->redirectPath())->with('success', '¡Correo electrónico verificado con éxito! Ya puedes acceder a todas las funciones.');
    }

    /**
     * Reenvía la notificación de verificación de correo electrónico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse([], 204)
                : redirect($this->redirectPath())->with('info', 'Tu correo ya está verificado.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $request->wantsJson()
            ? new JsonResponse([], 202)
            : back()->with('success', 'Se ha enviado un nuevo enlace de verificación a tu dirección de correo electrónico.');
    }
}
