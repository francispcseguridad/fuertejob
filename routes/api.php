<?php

use App\Http\Controllers\Api\Company\BonoPurchaseController;
use App\Http\Controllers\Api\Company\BonoCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí puede registrar rutas de API para su aplicación.
| Estas rutas son cargadas por el RouteServiceProvider
| y se les asigna el grupo de middleware "api".
|
*/

// Rutas protegidas para el catálogo y la compra de bonos
// El middleware 'auth:sanctum' asegura que solo usuarios autenticados (empresas) puedan acceder.
Route::middleware('auth:sanctum')->group(function () {
    // 1. Rutas del Catálogo de Bonos (GET para listar y ver detalles)
    Route::prefix('bonos/catalog')->group(function () {
        Route::get('/', [BonoCatalogController::class, 'index'])->name('api.bonos.catalog.index');
        Route::get('/{id}', [BonoCatalogController::class, 'show'])->name('api.bonos.catalog.show');
    });

    // 2. Rutas de Compras de Bonos (Compra y Listado de Historial)
    Route::prefix('bonos/purchases')->group(function () {
        Route::get('/', [BonoPurchaseController::class, 'index'])->name('api.bonos.purchases.index');
        Route::post('/purchase', [BonoPurchaseController::class, 'store'])->name('api.bonos.purchase.store');
    });

    // ... otras rutas de API protegidas ...
});

// Ruta pública para el callback (WebHook) de la pasarela de pago.
// Esta ruta no requiere autenticación ya que es llamada por el sistema de pago externo.
Route::match(['get', 'post'], '/bonos/callback/{purchase_id}', [BonoPurchaseController::class, 'handleCallback'])->name('api.bonos.callback');
