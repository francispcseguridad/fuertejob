<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;

class TrackPortalUsage
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Capturamos info de ruta antes de procesar para garantizar disponibilidad
        $route = $request->route() ?? app('router')->current();
        $routeName = $route?->getName()
            ?? app('router')->currentRouteName()
            ?? $route?->uri()
            ?? $request->path();
        $routeParams = $route?->parameters() ?? $route?->originalParameters() ?? [];

        // Procesar la petición primero
        $response = $next($request);

        // Registrar después de que la respuesta se haya generado (terminating middleware logic idealmente, 
        // pero aquí está bien si no retrasa mucho).
        // Para no bloquear, podríamos hacerlo dispatchable en un job, pero el requerimiento es directo.

        try {
            // Solo loguear métodos GET para no saturar con POSTs de formularios, 
            // aunque el usuario pidió "datos reales de uso", que incluye todo.
            // Loguearemos todo excepto lo evidente que sobra (assets, debugbar, etc si hubiera).
            // El servicio se encarga de filtrar o loguear.
            $this->analyticsService->logRequest($request, $routeName, $routeParams);
        } catch (\Exception $e) {
            // No fallar la petición si falla el log
            \Illuminate\Support\Facades\Log::error('Error logging analytics: ' . $e->getMessage());
        }

        return $response;
    }
}
