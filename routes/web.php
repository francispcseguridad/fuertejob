<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Worker\WorkerRegistrationController;
use App\Http\Controllers\Worker\WorkerProfileController;
use App\Http\Controllers\Worker\WorkerExperienceController;
use App\Http\Controllers\Worker\WorkerEducationController;
use App\Http\Controllers\Worker\WorkerSkillController;
use App\Http\Controllers\Worker\WorkerToolController;
use App\Http\Controllers\Worker\WorkerLanguageController;
use App\Http\Controllers\Worker\WorkerCvController;
use App\Http\Controllers\Worker\JobSearchController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PortalSettingController;
use App\Http\Controllers\Admin\WorkerManagementController;
use App\Http\Controllers\Admin\CompanyManagementController;
use App\Http\Controllers\Admin\InvoiceManagementController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\BonoCatalogController;
use App\Http\Controllers\Company\CompanyRegistrationController;
use App\Http\Controllers\Company\BonoController;
use App\Http\Controllers\Company\CreditController;
use App\Http\Controllers\Company\JobOfferController;
use App\Http\Controllers\Company\JobOfferAiController;
use App\Http\Controllers\Company\CompanyProfileController;
use App\Http\Controllers\Company\InvoiceController;
use App\Http\Controllers\Company\WorkerManagementForCompanyController;
use App\Http\Controllers\Company\CvController;
use App\Http\Controllers\Company\CandidateSelectionController;
use App\Http\Controllers\Company\CompanySectorController;
use App\Http\Controllers\Company\CompanyUserController;
use App\Http\Controllers\Company\SelectionProcessLogController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\Admin\AiPromptController;
use App\Http\Controllers\Admin\FaqItemController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\SocialNetworkController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Admin\CmsContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PublicJobSearchController;
use App\Http\Controllers\Admin\InmobiliariaController;
use App\Http\Controllers\Admin\AcademiaController;
use App\Http\Controllers\Admin\CommercialContactController as AdminCommercialContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\PublicInmobiliariaController;
use App\Http\Controllers\PublicAcademiaController;
use App\Http\Controllers\PublicSocialNetworkController;
use App\Http\Controllers\CommercialContactController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\AnalyticsModelController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\CanaryLocationController;
use App\Http\Controllers\Api\LocalidadController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('guest')->group(function () {
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Rutas de Registro de Candidatos
Route::get('/candidatos/alta', [WorkerRegistrationController::class, 'showRegistrationForm'])->name('worker.register.form');
Route::post('/candidatos/registro', [WorkerRegistrationController::class, 'register'])->name('worker.register.submit');
Route::get('/candidatos/gracias', [WorkerRegistrationController::class, 'finalizaraltausuario'])->name('verification.altausuario');

// Rutas de Registro de Empresas
Route::get('/empresa/alta', [CompanyRegistrationController::class, 'create'])->name('company.register.create');
Route::post('/empresa/registro', [CompanyRegistrationController::class, 'store'])->name('company.register.store');
Route::get('/empresa/gracias', function () {
    return view('company.auth.confirm');
})->name('company.register.success');

Route::get('/empleos', [PublicJobSearchController::class, 'index'])->name('public.jobs.index');
Route::get('/ofertas/{id}', [PublicJobSearchController::class, 'show'])->name('public.jobs.show');
Route::get('/inmobiliarias', [PublicInmobiliariaController::class, 'index'])->name('public.inmobiliarias.index');
Route::get('/formacion', [PublicAcademiaController::class, 'index'])->name('public.academias.index');
Route::get('/redes-sociales', [PublicSocialNetworkController::class, 'index'])->name('public.social_networks.index');
Route::get('/contacto', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store');
Route::post('/publicidad/contacto', [CommercialContactController::class, 'store'])->name('commercial_contacts.store');

Route::get('/novedades', [PortalController::class, 'blog'])->name('blog.index');
Route::get('/novedades/{slug}', [PortalController::class, 'blogShow'])->name('blog.show');
Route::get('/info/{slug}', [PortalController::class, 'info'])->name('info.show');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/api/localidades/search', [LocalidadController::class, 'search'])->name('api.localidades.search');

// Chatbot público (proxy server-side a Gemini)
Route::post('/chatbot/message', [ChatbotController::class, 'generate'])->name('chatbot.message');

// Autocompletado público de sectores (registro empresas)
Route::get('api/sectores/search', [CompanySectorController::class, 'search'])->name('api.sectores.search');

Route::auth(['register' => false]);
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/email/verificar', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])
        ->name('verification.resend');

    Route::get('api/habilidades/search', [WorkerSkillController::class, 'search'])->name('habilidades.search');
    Route::get('api/herramientas/search', [WorkerToolController::class, 'search'])->name('herramientas.search');
    Route::get('api/idiomas/search', [WorkerLanguageController::class, 'search'])->name('idiomas.search');
    Route::get('api/puestos/search-worker', [WorkerProfileController::class, 'searchJobPositions'])->name('puestos.search.worker');
    Route::get('api/sectores/search-worker', [WorkerProfileController::class, 'searchJobSectors'])->name('sectores.search.worker');
});

Route::get('cvs/{cv}', [CvController::class, 'serve'])->name('cvs.serve');

Route::middleware(['auth', 'rol:admin'])->prefix('administracion')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/perfil', [AdminController::class, 'profile'])->name('profile');
    Route::put('/perfil', [AdminController::class, 'updateProfile'])->name('profile.update');

    Route::resource('configuracion', PortalSettingController::class)->only(['index', 'update']);
    Route::resource('email-templates', EmailTemplateController::class)->except(['show', 'create']);

    Route::resource('candidatos', WorkerManagementController::class)->except(['create', 'store']);
    Route::put('candidatos/{solicitante}/password', [WorkerManagementController::class, 'updatePassword'])->name('candidatos.password.update');
    Route::post('candidatos/{candidato}/cv/reanalyze', [WorkerManagementController::class, 'reanalyzeCv'])->name('candidatos.cv.reanalyze');
    Route::post('candidatos/{candidato}/email', [WorkerManagementController::class, 'sendEmail'])->name('candidatos.email.send');

    Route::resource('empresas', CompanyManagementController::class)->except(['create', 'store']);
    Route::post('empresas/{empresa}/email', [CompanyManagementController::class, 'sendEmail'])->name('empresas.email.send');
    Route::put('empresas/{empresa}/password', [CompanyManagementController::class, 'updatePassword'])->name('empresas.password.update');

    Route::get('facturas', [InvoiceManagementController::class, 'index'])->name('facturas.index');
    Route::get('facturas/{invoice}/pdf', [InvoiceManagementController::class, 'showPdf'])->name('facturas.pdf');
    Route::post('facturas/{invoice}/rectificar', [InvoiceManagementController::class, 'rectify'])->name('facturas.rectificar');

    Route::resource('bonos', BonoCatalogController::class)->except(['create', 'edit']);
    Route::resource('ai_prompts', AiPromptController::class)->except(['create', 'edit']);

    Route::resource('faq-items', FaqItemController::class)->names('faq_items')->except(['show']);
    Route::resource('contenidos', CmsContentController::class)->names('cms_contents')->except(['show']);
    Route::resource('sectores', SectorController::class)->names('sectores')->parameters(['sectores' => 'sector'])->except(['show', 'create', 'edit']);

    Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class)->except(['show']);
    Route::resource('home_heroes', \App\Http\Controllers\Admin\HomeHeroController::class)->except(['show']);
    Route::resource('home_search_sections', \App\Http\Controllers\Admin\HomeSearchSectionController::class)->except(['show']);
    Route::resource('home_parallax_images', \App\Http\Controllers\Admin\HomeParallaxImageController::class)->except(['show']);
    Route::resource('home_locations', \App\Http\Controllers\Admin\HomeLocationController::class)->except(['show']);
    Route::resource('home_sectors', \App\Http\Controllers\Admin\HomeSectorController::class)->except(['show']);
    Route::resource('home_loop_texts', \App\Http\Controllers\Admin\HomeLoopTextController::class)->except(['show']);
    Route::resource('localidades', CanaryLocationController::class)->names('localidades')->parameters(['localidades' => 'canary_location'])->except(['show']);
    Route::resource('inmobiliarias', InmobiliariaController::class)->except(['show', 'create', 'edit']);
    Route::resource('academias', AcademiaController::class)->except(['show', 'create', 'edit']);
    Route::resource('social_networks', SocialNetworkController::class)->except(['show', 'create', 'edit']);

    Route::get('contacto', [ContactMessageController::class, 'index'])->name('contact_messages.index');
    Route::get('contacto/{contact_message}', [ContactMessageController::class, 'show'])->name('contact_messages.show');
    Route::post('contacto/{contact_message}/responder', [ContactMessageController::class, 'respond'])->name('contact_messages.respond');
    Route::get('contacto/{contact_message}/imprimir', [ContactMessageController::class, 'print'])->name('contact_messages.print');
    Route::resource('contactos-comerciales', AdminCommercialContactController::class)
        ->only(['index', 'show', 'update'])
        ->parameters(['contactos-comerciales' => 'contacto_comercial']);

    Route::get('ofertas/pendientes', [AdminController::class, 'pendingJobOffers'])->name('ofertas.pendientes');
    Route::post('ofertas/pendientes/{jobOffer}/aceptar', [AdminController::class, 'acceptPendingJobOffer'])->name('ofertas.pendientes.aceptar');
    Route::post('ofertas/pendientes/{jobOffer}/rechazar', [AdminController::class, 'rejectPendingJobOffer'])->name('ofertas.pendientes.rechazar');
    Route::get('ofertas', [AdminController::class, 'jobOffersIndex'])->name('ofertas.index');
    Route::post('ofertas', [AdminController::class, 'storeJobOffer'])->name('ofertas.store');
    Route::get('ofertas/{jobOffer}/editar', [AdminController::class, 'editJobOffer'])->name('ofertas.edit');
    Route::put('ofertas/{jobOffer}', [AdminController::class, 'updateJobOffer'])->name('ofertas.update');
    Route::get('ofertas/{jobOffer}/candidatos', [AdminController::class, 'jobOfferCandidates'])->name('ofertas.candidatos');
    Route::post('ofertas/{jobOffer}/candidatos/seleccionar', [AdminController::class, 'toggleCandidateSelection'])->name('ofertas.candidatos.seleccionar');
    Route::delete('ofertas/{jobOffer}/candidatos/{workerProfile}', [AdminController::class, 'removeCandidateFromOffer'])->name('ofertas.candidatos.remover');
    Route::get('ofertas/{id}/publica', [JobSearchController::class, 'show'])->name('ofertas.publica');
    Route::post('ofertas/{jobOffer}/aprobar', [AdminController::class, 'approveJobOffer'])->name('ofertas.aprobar');
    Route::post('ofertas/{jobOffer}/rechazar', [AdminController::class, 'rejectJobOffer'])->name('ofertas.rechazar');

    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/export', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/analytics/job-offer-views', [\App\Http\Controllers\Admin\AnalyticsController::class, 'jobOfferDailyViews'])
        ->name('analytics.job_offers.daily');
    Route::get('/analytics-models', [AnalyticsModelController::class, 'index'])->name('analytics_models.index');
    Route::post('/analytics-models/functions/{analyticsFunction}/link-bonos', [AnalyticsModelController::class, 'updateFunctionBonos'])
        ->name('analytics_models.functions.link_bonus');
    Route::get('/seguridad/contrasena', [SecurityController::class, 'showPasswordForm'])->name('security.password');
    Route::post('/seguridad/contrasena', [SecurityController::class, 'updatePassword'])->name('security.password.update');
});


Route::middleware(['auth', 'rol:empresa', 'empresa'])->prefix('empresa')->name('empresa.')->group(function () {
    Route::get('/dashboard', [CompanyProfileController::class, 'dashboard'])->name('dashboard');

    Route::get('trabajadores', [WorkerManagementForCompanyController::class, 'index'])->name('trabajadores.index');
    Route::get('trabajadores/{workerProfile}/oferta/{jobOffer}', [WorkerManagementForCompanyController::class, 'show'])->name('trabajadores.show');
    Route::post('trabajadores/{workerProfile}/oferta/{jobOffer}/desbloquear', [WorkerManagementForCompanyController::class, 'unlockCvView'])->name('trabajadores.unlock');

    Route::get('/perfil', [CompanyProfileController::class, 'index'])->name('profile.index');
    Route::match(['put', 'patch'], '/perfil', [CompanyProfileController::class, 'update'])->name('profile.update');
    Route::get('/perfil/sectores/search', [CompanySectorController::class, 'search'])->name('profile.sectors.search');
    Route::post('/perfil/sectores', [CompanySectorController::class, 'store'])->name('profile.sectors.store');
    Route::delete('/perfil/sectores/{sector}', [CompanySectorController::class, 'destroy'])->name('profile.sectors.destroy');

    // =========================================================================
    // RUTAS DE SELECCIÓN DE CANDIDATOS - REORGANIZADAS PARA EVITAR CONFLICTOS
    // =========================================================================

    // 1. Rutas específicas primero (más específicas a menos específicas)

    // Listar candidatos seleccionados de una oferta
    Route::get('/ofertas/{jobOffer}/candidatos-seleccionados', [CandidateSelectionController::class, 'indexSelected'])
        ->name('candidatos.seleccionados.index');

    // Ver/editar un candidato seleccionado específico
    Route::get('/ofertas/{jobOffer}/candidatos-seleccionados/{candidateSelection}', [CandidateSelectionController::class, 'editSelection'])
        ->name('candidatos.seleccionados.edit');

    // Toggle (marcar/desmarcar) selección
    Route::post('/candidatos/toggle-selection', [CandidateSelectionController::class, 'store'])
        ->name('candidatos.toggle_selection');

    // 2. ACTUALIZACIÓN DEL FORMULARIO PRINCIPAL DE SELECCIÓN (URL ESPECÍFICA)
    Route::post('/candidatos/seleccionados/{candidateSelection}/update', [CandidateSelectionController::class, 'updateSelection'])
        ->name('candidatos.seleccionados.update');

    // 3. RUTAS DEL LOG/HISTORIAL DEL PROCESO (PREFIJO ÚNICO PARA EVITAR CONFLICTO)
    Route::prefix('/candidatos/seleccionados/{candidateSelection}/historial')->name('candidatos.log.')->group(function () {
        // GET: Obtener todos los logs (AJAX)
        Route::get('/', [SelectionProcessLogController::class, 'index'])->name('index');

        // POST: Crear nuevo log (AJAX)
        Route::post('/', [SelectionProcessLogController::class, 'store'])->name('store');

        // PATCH: Actualizar log existente (AJAX)
        Route::post('/{log}', [SelectionProcessLogController::class, 'update'])->name('update');

        // DELETE: Eliminar log (AJAX)
        Route::delete('/{log}', [SelectionProcessLogController::class, 'destroy'])->name('destroy');
    });

    // =========================================================================
    // RUTAS DE OFERTAS Y MATCHING
    // =========================================================================

    // Catálogo de Bonos (Compra)
    Route::get('/bonos/catalogo', [BonoController::class, 'index'])->name('bonos.catalogo');
    Route::post('/bonos/contacto', [BonoController::class, 'contact'])->name('bonos.contact');

    Route::post('/ofertas/generar-descripcion', [JobOfferAiController::class, 'generateDescription'])
        ->name('ofertas.generate-description');
    Route::resource('ofertas', JobOfferController::class)->except(['show']);
    Route::post('ofertas/{oferta}/candidatos-coincidentes/desbloquear', [JobOfferController::class, 'unlockMatches'])
        ->name('ofertas.match.unlock');
    Route::post('ofertas/{oferta}/candidatos-coincidentes/{workerProfile}/desbloquear', [JobOfferController::class, 'unlockWorkerMatch'])
        ->name('ofertas.match.unlock.worker');
    Route::get('ofertas/{oferta}/candidatos-coincidentes', [JobOfferController::class, 'matchWorkers'])->name('ofertas.match');

    // Procesar la compra del bono (Llama a la lógica de Stripe/Ledger)
    Route::post('/bonos/{bono}/purchase', [BonoController::class, 'purchase'])->name('bonos.purchase');

    // Ver el saldo y el historial de crédito (Ledger)
    Route::get('/credito/saldo', [CreditController::class, 'index'])->name('credito.saldo');

    // Gestión de Facturas
    Route::get('/facturas', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/facturas/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');

    // Gestión de Usuarios Corporativos
    Route::get('/usuarios', [CompanyUserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios', [CompanyUserController::class, 'store'])->name('usuarios.store');

    // Ruta antigua que redirigía a la muestra del catálogo
    Route::get('/comprar', [CompanyProfileController::class, 'showBonoCatalog'])->name('comprar');
});


// =========================================================================
// RUTAS DEL CANDIDATO/TRABAJADOR (ROL: TRABAJADOR)
// =========================================================================

Route::middleware(['auth', 'rol:trabajador', 'verified'])->prefix('candidatos')->name('worker.')->group(function () {

    Route::get('/dashboard', [WorkerProfileController::class, 'dashboard'])->name('dashboard');

    // ----------------------------------------------------
    // GESTIÓN DEL PERFIL Y CV (WorkerProfileController)
    // ----------------------------------------------------
    Route::get('/perfil/editar', [WorkerProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/perfil/cv-completo', [WorkerProfileController::class, 'simplifiedEdit'])->name('profile.simplified');
    Route::put('/perfil', [WorkerProfileController::class, 'update'])->name('profile.update');
    Route::post('/perfil/cv-completo', [WorkerProfileController::class, 'simplifiedUpdate'])->name('profile.simplified.update');

    // Carga de CV
    Route::post('/perfil/cv/upload', [WorkerProfileController::class, 'uploadCv'])->name('cv.upload');

    // Eliminación de CV
    Route::delete('/perfil/cv/delete', [WorkerProfileController::class, 'deleteCv'])->name('cv.delete');

    // RE-ANÁLISIS DEL CV (La ruta que faltaba y causaba el error)
    // Usamos POST ya que modifica el estado (inicia el proceso de re-análisis)
    Route::post('/perfil/cv/reanalyze', [WorkerCvController::class, 'reAnalyzeCv'])->name('cv.reanalyze');

    // ----------------------------------------------------

    // Gestión de Experiencias
    Route::resource('experiencias', WorkerExperienceController::class)->except(['show']);

    // Gestión de Educación
    Route::resource('educacion', WorkerEducationController::class)->except(['show']);

    // Gestión de Habilidades
    // IMPORTANTE: La ruta de búsqueda debe ir ANTES del resource para evitar conflictos
    Route::resource('habilidades', WorkerSkillController::class);

    // Gestión de Herramientas
    // IMPORTANTE: La ruta de búsqueda debe ir ANTES del resource para evitar conflictos
    Route::resource('herramientas', WorkerToolController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Gestión de Idiomas
    // IMPORTANTE: La ruta de búsqueda debe ir ANTES del resource para evitar conflictos
    Route::resource('idiomas', WorkerLanguageController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Búsqueda de Ofertas
    Route::get('/ofertas', [JobSearchController::class, 'index'])->name('jobs.index');
    Route::get('/ofertas/{id}', [JobSearchController::class, 'show'])->name('jobs.show');
    Route::post('/ofertas/aplicar', [JobSearchController::class, 'apply'])->name('jobs.apply');
    Route::post('/ofertas/anular', [JobSearchController::class, 'cancel'])->name('jobs.cancel');
});

// =========================================================================
// RUTAS DE MENSAJERÍA (ACCESIBLE PARA TODOS LOS USUARIOS AUTENTICADOS)
// =========================================================================
Route::middleware(['auth'])->prefix('mensajes')->name('messaging.')->group(function () {
    Route::get('/', [MessagingController::class, 'index'])->name('inbox');
    Route::post('/contactar-seleccion', [MessagingController::class, 'contactCandidateFromSelection'])->name('contact.selection');
    Route::get('/crear/{recipientId}/{resourceType}/{resourceId}', [MessagingController::class, 'startThread'])->name('start');
    Route::post('/ajax/crear', [MessagingController::class, 'startThreadAjax'])->name('start_ajax');
    Route::get('/{thread}', [MessagingController::class, 'show'])->name('show');
    Route::post('/{thread}', [MessagingController::class, 'sendMessage'])->name('send');
});

// =========================================================================
// RUTA RAÍZ
// =========================================================================
// Route::get('/', function () {
//     return view('welcome');
// });

// =========================================================================
// RUTA DE FAQS (API PÚBLICA)
// =========================================================================
Route::get('/api/faqs/worker', [App\Http\Controllers\FaqController::class, 'getWorkerFaqs'])->name('api.faqs.worker');
Route::get('/api/faqs/company', [App\Http\Controllers\FaqController::class, 'getCompanyFaqs'])->name('api.faqs.company');

// =========================================================================
// RUTA RAÍZ
// =========================================================================
Route::get('/', [PortalController::class, 'index'])->name('home');



Route::get('/salir', function () {
    Auth::logout();
    return redirect('/'); // Redirige a la página principal o a donde desees
})->name('salir');
