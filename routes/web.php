<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PatrolController;
use App\Http\Controllers\PatrolAnalyticsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\ExecutiveAnalyticsController;
use App\Http\Controllers\GuardDetailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantationController;
use App\Http\Controllers\DynamicLabelsController;
use App\Http\Controllers\AnukampaController;
use App\Http\Controllers\BeatFeatureController;
use App\Http\Controllers\BeatMapController;
use App\Http\Controllers\ClientDetailsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\GuardsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ForestController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\GlobalSuperAdminController;
use App\Http\Controllers\ForestReportConfigController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PatrollingController;
use App\Http\Controllers\PatrolAnalysisController;
use App\Http\Controllers\WebBoundaryController;
use App\Http\Controllers\GuardReportController;
use App\Http\Controllers\IncidenceController;
/* Auth Routes */

Route::get('/login', [AuthController::class , 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class , 'login']);
Route::post('/logout', [AuthController::class , 'logout'])->name('logout');

/* Root redirect - redirect to login if not authenticated */
Route::get('/', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = session('user');

    if ($user && (int)$user->role_id === 8) {
        return redirect()->route('global.dashboard');
    }

    return redirect()->route('dashboard');
});



/* Protected Routes - Require Authentication */
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class , 'index']); // Alias for home


    Route::get('/global-dashboard', [GlobalSuperAdminController::class , 'dashboard'])
        ->name('global.dashboard');


    /* Profile */
    Route::get('/profile/{user}', [ProfileController::class , 'show'])->name('profile');

    /* API Routes */
    Route::prefix('api')->group(
        function () {
            Route::get('/guard-details/{guardId}', [GuardDetailController::class , 'getGuardDetails']);
            Route::get('/patrol-session/{sessionId}', [PatrolController::class , 'getSessionDetails']);
        }
        );

        /* Executive Analytics */
        Route::get('/analytics/executive', [ExecutiveAnalyticsController::class , 'executiveDashboard'])->name('analytics.executive');
        Route::get('/analytics/executive/api/kpis', [ExecutiveAnalyticsController::class , 'getKPIsApi'])->name('analytics.executive.api.kpis');

        /* Debug Route - Remove in production */
        Route::get(
            '/debug/db-test',
            function () {
            try {
                $pdo = DB::connection()->getPdo();
                $user = session('user');
                $companyId = ($user && isset($user->company_id)) ? $user->company_id : 56;

                $usersCount = DB::table('users')->where('company_id', $companyId)->count();
                $sitesCount = DB::table('site_details')->where('company_id', $companyId)->count();
                $patrolsCount = DB::table('patrol_sessions')->where('company_id', $companyId)->count();

                return response()->json([
                'status' => 'success',
                'database' => 'connected',
                'company_id' => $companyId,
                'counts' => [
                'users' => $usersCount,
                'sites' => $sitesCount,
                'patrols' => $patrolsCount,
                ]
                ]);
            }
            catch (\Exception $e) {
                return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
                ], 500);
            }
        }
        );



        /* Patrol */
        Route::prefix('patrol')->group(
            function () {
            Route::get('/foot-summary', [PatrolController::class , 'footSummary'])->name('patrol.foot.summary');
            Route::get('/night-summary', [PatrolController::class , 'nightSummary'])->name('patrol.night.summary');
            Route::get('/night-explorer', [PatrolController::class , 'nightExplorer'])->name('patrol.night.explorer');
            Route::get('/analytics', [PatrolAnalyticsController::class , 'patrolAnalytics'])->name('patrol.analytics');
            Route::get('/foot-explorer', [PatrolController::class , 'footExplorer'])->name('patrol.foot.explorer');
            Route::get('/foot/guard-distance', [PatrolController::class , 'footDistanceByGuard'])->name('patrol.foot.guard.distance');
            Route::get('/maps', [PatrolController::class , 'kmlView'])->name('patrol.kml.view');
            Route::get('/guard-details/{id}', [PatrolController::class , 'guardDetailsApi'])->name('patrol.guard.details.api');
            Route::get('/api/filtered-data', [PatrolController::class , 'getFilteredData'])->name('patrol.api.filtered.data');
            Route::get('/type/{type}', [ExecutiveAnalyticsController::class , 'getPatrolsByType'])->name('patrol.by-type');
        }
        );

        /* Analytical Reports Hub */
        Route::prefix('reports')->group(
            function () {
            Route::get('/monthly', [ReportController::class , 'monthly'])->name('reports.monthly');
            Route::get('/camera-tracking', [ReportController::class , 'cameraTracking']);
        }
        );



        /* Filter API Routes */
        Route::get('/filters/beats/{rangeId?}', [FilterController::class , 'beats']);
        Route::get('/filters/users', [FilterController::class , 'users']);
        Route::get('/filters/guards/autocomplete', [FilterController::class , 'guardAutocomplete']);
        Route::get('/filters/compartments/{beat}', [FilterController::class , 'compartments']);

        /* KPI Modal API Routes */
        Route::get('/api/active-guards', [ExecutiveAnalyticsController::class , 'getActiveGuards']);
        Route::get('/api/beats-details', [ExecutiveAnalyticsController::class , 'getBeatsDetails']);
        Route::get('/api/coverage-analysis', [ExecutiveAnalyticsController::class , 'getCoverageAnalysisApi']);
        Route::get('/api/patrol-analytics', [ExecutiveAnalyticsController::class , 'getPatrolAnalyticsApi']);
        Route::get('/api/patrols-by-type', [ExecutiveAnalyticsController::class , 'getPatrolDetailsByTypeApi']);
        Route::get('/api/incidents-details', [ExecutiveAnalyticsController::class , 'getIncidentsDetailsApi']);
        Route::get('/api/distance-details', [ExecutiveAnalyticsController::class , 'getDistanceDetailsApi']);
        Route::get('/api/attendance-details', [ExecutiveAnalyticsController::class , 'getAttendanceDetailsApi']);


        /* Incidents */
        Route::prefix('incidents')->group(
            function () {
            Route::get('/summary', [IncidentController::class , 'summary'])->name('incidents.summary');
            // Route::get('/nearby', [IncidentController::class, 'nearby'])->name('incidents.nearby');
            Route::get('/{id}/details', [IncidentController::class , 'getIncidentDetails'])->name('incidents.details');
            Route::get('/type/{type?}', [IncidentController::class , 'getIncidentsByType'])->name('incidents.by-type');
        }
        );


        /* Guard Details (Web View) */
        Route::get('/guard-details/{id}', [PatrolController::class , 'guardDetails'])->name('guard.details');
    });


Route::prefix('dynamic-labels')->group(function () {

    Route::get('/', [DynamicLabelsController::class , 'index']);

    Route::post('/master', [DynamicLabelsController::class , 'storeMaster']);

    Route::post('/master/update/{id}', [DynamicLabelsController::class , 'updateMaster']);

    Route::get(
        '/company/{companyId}',
    [DynamicLabelsController::class , 'editCompany']
    );

    Route::post(
        '/company/{companyId}',
    [DynamicLabelsController::class , 'saveCompany']
    );

    Route::post('/master/delete/{id}', [DynamicLabelsController::class , 'deleteMaster']);
});


Route::prefix('anukampa')->group(function () {
    Route::get('/dashboard', [AnukampaController::class , 'dashboard'])->name('anukampa.dashboard');
    Route::get('/claims', [AnukampaController::class , 'index'])->name('anukampa.claims');
    Route::post('/claims', [AnukampaController::class , 'store'])->name('anukampa.store');

    Route::get('/claims/{id}', [AnukampaController::class , 'show'])->name('anukampa.show');
    Route::get('/claims/{id}/edit', [AnukampaController::class , 'edit'])->name('anukampa.edit');
    Route::put('/claims/{id}', [AnukampaController::class , 'update'])->name('anukampa.update');
    Route::patch('/claims/{id}/status', [AnukampaController::class , 'updateStatus'])->name('anukampa.updateStatus');
});

Route::prefix('forest')->group(function () {
    // Dashboard view
    Route::get('/beat-features', [BeatFeatureController::class , 'dashboard'])->name('beat_features.dashboard');

    // Form submission for adding a new feature
    Route::post('/beat-features', [BeatFeatureController::class , 'store'])->name('beat_features.store');
});


Route::controller(BeatMapController::class)->group(function () {

    Route::get('/forest/know-your-area', 'forestIndex')->name('know-your-area.forest');

    Route::get('/normal/know-your-area', 'normalIndex')->name('know-your-area.normal');

    Route::get('/get-map-data', 'getMapData')->name('know-your-area.data');
});




// =========================================================================
// ClientDetailsController Routes
// =========================================================================
Route::controller(ClientDetailsController::class)->group(function () {
    Route::get('/sites', 'index')->name('sites');
    Route::get('/clientsview/{clientId}', 'clientView')->name('clients.view');
    Route::get('/clients/export', 'export')->name('clients.export');
    Route::get('/clients', 'index')->name('clients');
    Route::get('/clients/getclients/', 'getClients')->name('clients.getclients');
    Route::get('/clients/create', 'create')->name('clients.create');
    Route::post('/clients/createaction', 'create_action')->name('clients.createaction');
    Route::get('/clients/getCity/{id}', 'getCity')->name('clients.getCity');
    Route::get('/clients/active/{id}', 'statusActive')->name('clients.active');
    Route::get('/clients/inactive/{id}', 'statusInactive')->name('clients.inactive');
    Route::get('/clients/deleteClient/{id}', 'deleteClient')->name('clients.deleteClient');

    Route::get('/clients/editClient/{id}', 'editClient')->name('clients.editClient');
    Route::post('/clients/editaction/{id}', 'editaction')->name('clients.editaction');

    Route::get('/clients/getshifts/{client_id}/{site_id}', 'getShifts')->name('clients.getshifts');
    Route::get('/clients/getshiftscreate/{client_id}/{site_id}', 'getShiftsCreate')->name('clients.getshiftscreate');
    Route::post('/clients/shift_createaction/{client_id}/{site_id}', 'shift_createaction')->name('clients.shift_createaction');
    Route::get('/clients/shift_delete/{id}/{client_id}/{site_id}', 'ShiftsDelete')->name('clients.shift_delete');
    Route::get('/clients/shift_edit/{id}/{client_id}/{site_id}', 'ShiftsEdit')->name('clients.shift_edit');
    Route::post('/clients/shift_updateaction/{id}/{client_id}/{site_id}', 'shift_updateaction')->name('clients.shift_updateaction');

    Route::get('/clients/getclientgeofences/{client_id}/{site_id}', 'getClientGeofence')->name('clients.getclientgeofences');
    Route::get('/clients/geofence_create/{client_id}/{site_id}', 'getGeofenceCreate')->name('clients.geofence_create');
    Route::post('/clients/geofencestore/{client_id}/{site_id}', 'Geofencestore')->name('clients.geofencestore');
    Route::get('/clients/geofence_edit/{client_id}/{site_id}/{id}', 'geofence_edit')->name('clients.geofence_edit');
    Route::post('/clients/geofence_editaction/{client_id}/{site_id}/{id}', 'geofenceEditAction')->name('clients.geofence_editaction');
    Route::get('/clients/geofence_delete/{client_id}/{site_id}/{id}', 'GeofenceDelete')->name('clients.geofence_delete');

    Route::get('/clients/getclientguards/{client_id}/{site_id}', 'getClientGuards')->name('clients.getclientguards');
    Route::get('/clients/clientguard_create/{client_id}/{site_id}', 'getClientGuardsCreate')->name('clients.clientguard_create');
    Route::post('/clients/guard_createaction/{client_id}/{site_id}', 'guard_createaction')->name('clients.guard_createaction');
    Route::get('/clients/clientguard_read/{client_id}/{site_id}/{id}', 'getGuardRead')->name('clients.clientguard_read');
    Route::get('/clients/clientguard_edit/{client_id}/{site_id}/{id}', 'clientguard_edit')->name('clients.clientguard_edit');
    Route::post('/clients/clientguard_editaction/{client_id}/{site_id}/{id}', 'clientguard_editaction')->name('clients.clientguard_editaction');
    Route::get('/clients/getNotAssignGuard/{shift_id}', 'getNotAssignGuard')->name('getNotAssignGuard');
    Route::get('/clients/guardDelete/{client_id}/{site_id}/{id}', 'guardDelete')->name('clients.guardDelete');

    Route::get('/clients/gettours/{id}', 'getClientTour')->name('clients.gettours');
    Route::get('/clients/guardtourcheckpoint/{id}', 'getGuardTourCheckpoint')->name('clients.guardtourcheckpoint');
    Route::post('/tourDetailsExport', 'tourDetailsExport')->name('tourDetailsExport');
    Route::get('complaints/', 'complaints')->name('complaints');
    Route::get('raiseComplaint/', 'raiseComplaint')->name('raiseComplaint');
    Route::post('complaintAction/', 'complaintAction')->name('complaintAction');
    Route::get('complaintResolved/{notificationId}', 'complaintResolved')->name('complaintResolved');
});

// =========================================================================
// SiteController Routes
// =========================================================================
Route::controller(SiteController::class)->group(function () {
    Route::get('/sites/getsites/{id}', 'getSites')->name('sites.getsites');

    // Note: '/sites/{id}' had the same route name ('sites') as ClientDetailsController's '/sites' route.
    // I changed the name to 'sites.show' to prevent collision.
    Route::get('/sites/{id}', 'index')->name('sites.show');

    Route::get('/sites/getSupervisorSites/{id}', 'getSupervisorSites')->name('sites.getSupervisorSites');

    Route::get('/sites/site_create/{id}', 'siteCreate')->name('sites.site_create');
    Route::post('/sites/site_createaction/{id}', 'site_createaction')->name('sites.site_createaction');
    Route::get('/sites/site_edit/{client_id}/{id}', 'site_edit')->name('sites.site_edit');
    Route::get('/sites/site_view/{client_id}/{id}', 'site_view')->name('sites.site_view');

    Route::post('/sites/site_editaction/{client_id}/{id}', 'site_editaction')->name('sites.site_editaction');
    Route::get('/sites/site_delete/{client_id}/{id}', 'site_delete')->name('sites.site_delete');
    Route::get('/getClientSites/{client_id}', 'getClientSites')->name('getClientSites');

    Route::get('playBackOfGuards/{siteId}', 'playBackOfGuards')->name('playBackOfGuards');
    Route::get('playBack/{userId}', 'playBack')->name('playBack');
    Route::get('updateGuardLocation', 'updateGuardLocation')->name('updateGuardLocation');

    Route::get('export/{clientID}', 'export')->name('sites.export');
    Route::get('success', 'success')->name('success');
});


Route::controller(SupervisorController::class)->group(function () {
    Route::get('siteRelease/{supervisorId}/{siteId}', 'siteRelease')->name('siteRelease');
});

Route::get('/check-me', function () {
    return "AG-CHECK: " . date('Y-m-d H:i:s') . " - Codebase is being updated.";
});

Route::controller(GuardsController::class)->group(function () {

    Route::get('/guards', 'index')->name('guards');
    Route::get('/guards/guardEdit/{clientId}/{id}', 'guardEdit')->name('guards.guard_edit');
    Route::get('/guards/{id}', 'getSites')->name('guards.getsites');
    Route::get('/guards/getShifts/{id}', 'getShifts')->name('guards.getshifts');
    Route::post('/guards/editAction/{id}', 'editAction')->name('guards.editaction');

    Route::get('/unAssignGuards', 'unAssignGuards')->name('unAssignGuards');
    Route::get('/unassigned/guard_export', 'unassigned_export')->name('unassigned_guard.export');
    Route::get('/guards/guardexport/exp', 'assignedExport')->name('assigned.export');

    Route::get('/fetchClients', 'fetchClients')->name('fetchClients');
    Route::get('/fetchSites', 'fetchSites')->name('fetchSites');
    Route::get('/fetchCheckIn', 'fetchCheckIn')->name('fetchCheckIn');
    Route::get('/fetchLateShow', 'fetchLateShow')->name('fetchLateShow');
    Route::get('/fetchNoShow', 'fetchNoShow')->name('fetchNoShow');
    Route::get('/fetchLog', 'fetchLog')->name('fetchLog');
});


Route::prefix('attendance')->group(function () {

    Route::get('/explorer', [AttendanceController::class , 'explorer'])
        ->name('attendance.explorer');

    Route::get('/logs', [AttendanceController::class , 'logs'])
        ->name('attendance.logs');

    Route::get('/requests', [AttendanceController::class , 'requests'])
        ->name('attendance.requests');
    Route::post('/requests/{id}/approve', [AttendanceController::class , 'approveRequest']);
    Route::post('/requests/{id}/reject', [AttendanceController::class , 'rejectRequest']);


    Route::get('/map', [AttendanceController::class , 'mapView'])
        ->name('attendance.map');

    Route::get('/export', [AttendanceController::class , 'export'])
        ->name('attendance.export');
});


Route::prefix('plantation')->group(function () {

    Route::get('/dashboard', [PlantationController::class , 'dashboard'])->name('plantation.dashboard');
    Route::get('/analytics', [PlantationController::class , 'analytics'])->name('plantation.analytics');

    Route::get('/create', [PlantationController::class , 'create'])->name('plantation.create');
    Route::post('/store', [PlantationController::class , 'store'])->name('plantation.store');

    Route::get('/view/{id}', [PlantationController::class , 'show'])->name('plantation.show');
    Route::get('/workflow/{id}', [PlantationController::class , 'workflow'])->name('plantation.workflow');
    Route::post('/workflow/{id}', [PlantationController::class , 'saveWorkflow'])->name('plantation.workflow.save');
});



Route::prefix('guards')->group(function () {

    Route::get('/', [GuardsController::class , 'index'])->name('guards');

    Route::get('/guardEdit/{clientId}/{id}', [GuardsController::class , 'guardEdit'])
        ->name('guards.guard_edit');

    Route::get('/{id}', [GuardsController::class , 'getSites'])
        ->name('guards.getsites');

    Route::get('/getShifts/{id}', [GuardsController::class , 'getShifts'])
        ->name('guards.getshifts');

    Route::post('/editAction/{id}', [GuardsController::class , 'editAction'])
        ->name('guards.editaction');

    Route::get('/guardexport/exp', [GuardsController::class , 'assignedExport'])
        ->name('assigned.export');
});


Route::get('/unAssignGuards', [GuardsController::class , 'unAssignGuards'])
    ->name('unAssignGuards');

Route::get('/unassigned/guard_export', [GuardsController::class , 'unassigned_export'])
    ->name('unassigned_guard.export');


Route::prefix('fetch')->group(function () {

    Route::get('/clients', [GuardsController::class , 'fetchClients'])->name('fetchClients');

    Route::get('/sites', [GuardsController::class , 'fetchSites'])->name('fetchSites');

    Route::get('/checkin', [GuardsController::class , 'fetchCheckIn'])->name('fetchCheckIn');

    Route::get('/lateshow', [GuardsController::class , 'fetchLateShow'])->name('fetchLateShow');

    Route::get('/noshow', [GuardsController::class , 'fetchNoShow'])->name('fetchNoShow');

    Route::get('/log', [GuardsController::class , 'fetchLog'])->name('fetchLog');
});

Route::prefix('forest')->group(function () {

    Route::get('/dashboard', [ForestController::class , 'index'])
        ->name('forest.olddashboard');

    Route::get('/live', [ForestController::class , 'liveData'])
        ->name('forest.live');

    Route::get('/user-summary', [ForestController::class , 'userSummary'])
        ->name('forest.userSummary');
});

Route::get('/users/edit/{id}', [UsersController::class , 'edit'])
    ->name('users.edit');

Route::post('/users/update/{id}', [UsersController::class , 'update'])
    ->name('users.update');

Route::prefix('modules')->group(function () {

    Route::get('/', [ModuleController::class , 'index'])
        ->name('modules.index');

    Route::post('/update', [ModuleController::class , 'update'])
        ->name('modules.update');
});



Route::get('/companies/create', [GlobalSuperAdminController::class , 'createCompany'])
    ->name('companies.create');

Route::post('/companies/store', [GlobalSuperAdminController::class , 'storeCompany'])
    ->name('companies.store');

Route::get(
    '/companies/{id}/edit',
[GlobalSuperAdminController::class , 'editCompany']
)->name('companies.edit');

Route::post(
    '/companies/{id}/update',
[GlobalSuperAdminController::class , 'updateCompany']
)->name('companies.update');


Route::prefix('global')->group(function () {

    Route::get('/superadmins', [GlobalSuperAdminController::class , 'superAdmins'])
        ->name('global.superadmins');

    Route::get('/superadmins/{id}', [GlobalSuperAdminController::class , 'viewSuperAdmin'])
        ->name('global.superadmins.view');

    Route::get('/admins', [GlobalSuperAdminController::class , 'admins'])
        ->name('global.admins');

    Route::get('/admins/{id}', [GlobalSuperAdminController::class , 'viewAdmin'])
        ->name('global.admins.view');

    Route::get('/users/edit/{id}', [GlobalSuperAdminController::class , 'editUser'])
        ->name('global.users.edit');

    Route::post('/users/update/{id}', [GlobalSuperAdminController::class , 'updateUser'])
        ->name('global.users.update');
    Route::get('/companies', [GlobalSuperAdminController::class , 'companies'])
        ->name('global.companies');
    Route::get('/enter-simulation/{id}', [GlobalSuperAdminController::class , 'viewCompanyDashboard'])->name('global.enter_simulation');
    Route::get('/exit-simulation', [GlobalSuperAdminController::class , 'exitCompanyDashboard'])->name('global.exit_simulation');

    Route::prefix('dynamic-labels')->group(function () {

            Route::get('/', [DynamicLabelsController::class , 'index']);

            Route::post('/master', [DynamicLabelsController::class , 'storeMaster']);

            Route::post('/master/update/{id}', [DynamicLabelsController::class , 'updateMaster']);

            Route::get(
                '/company/{companyId}',
            [DynamicLabelsController::class , 'editCompany']
            );

            Route::post(
                '/company/{companyId}',
            [DynamicLabelsController::class , 'saveCompany']
            );

            Route::post('/master/delete/{id}', [DynamicLabelsController::class , 'deleteMaster']);
        }
        );
    });
Route::prefix('report-configs')
    ->name('report-configs.')
    ->controller(ForestReportConfigController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/reports-table', 'reportsTable')->name('table');
        Route::get('/reports/{id}', 'show')->name('show');
        Route::post('/reports/{id}/update-status', 'updateStatus')->name('updateStatus');
        // 🔥 THIS WAS MISSING
        Route::get('/reports-dashboard', 'reportsDashboard')->name('dashboard');
    });

Route::prefix('registrations')->group(function () {

    Route::get('/', [RegistrationController::class , 'index'])
        ->name('registrations.index');

    Route::get('/create', [RegistrationController::class , 'create'])
        ->name('registrations.create');

    Route::post('/store', [RegistrationController::class , 'store'])
        ->name('registrations.store');

    Route::get('/{id}/edit', [RegistrationController::class , 'edit'])
        ->name('registrations.edit');

    Route::post('/{id}/update', [RegistrationController::class , 'update'])
        ->name('registrations.update');

    Route::get('/{id}/destroy', [RegistrationController::class , 'destroy'])
        ->name('registrations.destroy');

    Route::get('/export', [RegistrationController::class , 'export'])
        ->name('registrations.export');

});

Route::get('/patrol-log/{flag}', [PatrollingController::class , 'logs'])
    ->name('patrolling.log');

Route::get('/patrol-log/{id}/details', [PatrollingController::class , 'logDetails'])
    ->name('patrolling.log.details');




/* -------------------------
 PATROLLING REPORTS -------------------------*/

Route::post('/downloadPatrollingStatusReport', [ReportController::class , 'downloadPatrollingStatusReport'])
    ->name('downloadPatrollingStatusReport');

Route::post('/patrollingSummaryDownload', [ReportController::class , 'patrollingSummaryDownload'])
    ->name('patrollingSummaryDownload');

Route::post('/patrollingLogsReportDownload', [ReportController::class , 'patrollingLogsReportDownload'])
    ->name('patrollingLogsReportDownload');


/* -------------------------
 PATROL ANALYSIS -------------------------*/

Route::prefix('patrol-analysis')->group(function () {

    Route::get('/dashboard', [PatrolAnalysisController::class , 'dashboard'])
        ->name('patrolling.dashboard');

    Route::get('/analytics-dashboard', [PatrolAnalysisController::class , 'analyticsDashboard'])
        ->name('patrolling.analytics.dashboard');

    Route::get('/analytics-pro', [PatrolAnalysisController::class , 'analyticsPro'])
        ->name('patrolling.analytics.pro');

    Route::get('/analytics/pdf', [PatrolAnalysisController::class , 'analyticsPdf'])
        ->name('patrolling.analytics.pdf');

    Route::get('/analytics-pro-advanced', [PatrolAnalysisController::class , 'analyticsProAdvanced'])
        ->name('patrolling.analytics.pro.advanced');

    Route::get('/analytics/user/{id}/drilldown', [PatrolAnalysisController::class , 'userDrilldown'])
        ->name('patrolling.analytics.user.drilldown');

    Route::get('/analytics/site/{id}/drilldown', [PatrolAnalysisController::class , 'siteDrilldown'])
        ->name('patrolling.analytics.site.drilldown');


    /* -------- EXPORTS -------- */

    Route::get('/analytics/export/csv', [PatrolAnalysisController::class , 'exportCsv'])
        ->name('patrolling.analytics.export.csv');

    Route::get('/analytics/export/excel', [PatrolAnalysisController::class , 'exportExcel'])
        ->name('patrolling.analytics.export.excel');

    Route::get('/analytics/export/pdf', [PatrolAnalysisController::class , 'exportPdf'])
        ->name('patrolling.analytics.export.pdf');


    /* -------- LIVE DATA -------- */

    Route::get('/analytics/live', [PatrolAnalysisController::class , 'liveData'])
        ->name('patrolling.analytics.live');

});


Route::controller(WebBoundaryController::class)->group(function () {

    Route::get('/forest/boundaries', 'index')->name('forest.boundaries');
    Route::get('/forest/boundaries/data', 'getMapData')->name('forest.boundaries.data');

    Route::get('/normal/boundaries', 'normalIndex')->name('normal.boundaries');
    Route::get('/normal/boundaries/data', 'getMapData')->name('normal.boundaries.data');

    Route::get('/boundary/sections/{rangeId}', 'getSections')->name('boundary.sections');
    Route::get('/boundary/beats/{sectionId}', 'getBeats')->name('boundary.beats');

});


/* -------------------------
 PATROLLING -------------------------*/

Route::prefix('patrolling')->group(function () {

    Route::get('/', [PatrollingController::class , 'index'])->name('patrolling');

    Route::get('/analysis', [PatrollingController::class , 'analysis'])
        ->name('patrolling.analysis');

    Route::get('/details/{patrol}', [PatrollingController::class , 'show'])
        ->name('patrolling.details');

    Route::get('/create', [PatrollingController::class , 'create'])
        ->name('patrolling.create');

    Route::post('/', [PatrollingController::class , 'store'])
        ->name('patrolling.store');
});




Route::post('downloadClientWiseReport', [ReportController::class , 'downloadClientWiseReport'])->name('downloadClientWiseReport');

Route::get('downloadDailyTour', [ReportController::class , 'downloadDailyTour'])->name('downloadDailyTour');

Route::get('IncidenceReport/{fromDate}/{toDate}/{geofences}/{priority}/{incidentSubType}', [ReportController::class , 'IncidenceReport'])->name('IncidenceReport');

Route::post('downloadIncidenceReport', [ReportController::class , 'downloadIncidenceReport'])->name('downloadIncidenceReport');

Route::get('VisitorReport/{fromDate}/{toDate}/{geofences}/{incidentSubType}', [ReportController::class , 'VisitorReport'])->name('VisitorReport');

Route::get('downloadVisitorReport', [ReportController::class , 'downloadVisitorReport'])->name('downloadVisitorReport');

Route::get('downloadUserAttendanceReport', [ReportController::class , 'downloadUserAttendanceReport'])->name('downloadUserAttendanceReport');

Route::post('downloadSiteWiseGuardReport', [ReportController::class , 'downloadSiteWiseGuardReport'])->name('downloadSiteWiseGuardReport');

Route::get('downloadVisitorReportCount', [ReportController::class , 'downloadVisitorReportCount'])->name('downloadVisitorReportCount');

Route::get('downloadWorkingSummaryReport', [ReportController::class , 'downloadWorkingSummaryReport'])->name('downloadWorkingSummaryReport');

Route::get('visitors/{date}/{siteId}', [ReportController::class , 'visitorSummaryReport'])->name('visitors');

Route::get('downloadVisitorSummaryReport', [ReportController::class , 'downloadVisitorSummaryReport'])->name('downloadVisitorSummaryReport');

Route::get('TourReport/{fromDate}/{toDate}/{geofences}/{tourSubType}/{userId}', [ReportController::class , 'TourReport'])->name('TourReport');

Route::get('DayTour/{tourDate}/{geofences}/{tourSubType}/{userId}', [ReportController::class , 'DayTour'])->name('DayTour');

Route::get('tourSummary/{date}/{tourId}/{siteId}', [ReportController::class , 'tourSummary'])->name('tourSummary');

Route::get('downloadTourSummary', [ReportController::class , 'downloadTourSummary'])->name('downloadTourSummary');

Route::get('incidenceSummary/{date}/{type}/{geofences}', [ReportController::class , 'incidenceSummary'])->name('incidenceSummary');

Route::post('downloadIncidenceSummary', [ReportController::class , 'downloadIncidenceSummary'])->name('downloadIncidenceSummary');

Route::get('guardAttendanceReport/{guardId}/{fromDate}/{toDate}', [ReportController::class , 'guardAttendanceReport'])->name('guardAttendanceReport');

Route::get('AttendanceReport/{fromDate}/{toDate}/{geofences}/{attendanceSubType}/{userId}/{supervisor}', [ReportController::class , 'AttendanceReport'])->name('AttendanceReport');

Route::get('downloadGuardReport', [ReportController::class , 'downloadGuardReport'])->name('downloadGuardReport');

Route::get('downloadTourDayWise', [ReportController::class , 'downloadTourDayWise'])->name('downloadTourDayWise');

Route::get('singleDayTour/{guardTourLogId}/{date}', [ReportController::class , 'singleDayTour'])->name('singleDayTour');

Route::post('downloadsingleDayTour', [ReportController::class , 'downloadsingleDayTour'])->name('downloadsingleDayTour');

Route::get('downloadGuardTourReport', [ReportController::class , 'downloadGuardTourReport'])->name('downloadGuardTourReport');

Route::post('downloadEmergencyAttendance', [ReportController::class , 'downloadEmergencyAttendance'])->name('downloadEmergencyAttendance');

Route::post('downloadOnSiteReport', [ReportController::class , 'downloadOnSiteReport'])->name('downloadOnSiteReport');

Route::post('downloadForgotToMarkExit', [ReportController::class , 'downloadForgotToMarkExit'])->name('downloadForgotToMarkExit');

Route::post('downloadPerformanceReport', [ReportController::class , 'downloadPerformanceReport'])->name('downloadPerformanceReport');

Route::get('PerformanceReport/{fromDate}/{toDate}/{geofences}', [ReportController::class , 'PerformanceReport'])->name('PerformanceReport');

Route::post('downloadAllGuardAttendance', [ReportController::class , 'downloadAllGuardAttendance'])->name('downloadAllGuardAttendance');

Route::post('downloadAbsentReport', [ReportController::class , 'downloadAbsentReport'])->name('downloadAbsentReport');

Route::post('downloadLateReport', [ReportController::class , 'downloadLateReport'])->name('downloadLateReport');

Route::post('downloadClientVisitReport', [ReportController::class , 'downloadClientVisitReport'])->name('downloadClientVisitReport');

Route::post('downloadAllSupervisorAttendance', [ReportController::class , 'downloadAllSupervisorAttendance'])->name('downloadAllSupervisorAttendance');

Route::post('downloadTourDiaryReport', [ReportController::class , 'downloadTourDiaryReport'])->name('downloadTourDiaryReport');

Route::post('downloadSelfTourDiaryReport', [ReportController::class , 'downloadSelfTourDiaryReport'])->name('downloadSelfTourDiaryReport');

Route::post('downloadSuperVisorTourDiaryReport', [ReportController::class , 'downloadSuperVisorTourDiaryReport'])->name('downloadSuperVisorTourDiaryReport');

Route::post('downloadAdminTourDiaryReport', [ReportController::class , 'downloadAdminTourDiaryReport'])->name('downloadAdminTourDiaryReport');


Route::prefix('report')->controller(GuardReportController::class)->group(function () {

    Route::get('modal-view', 'reportModalView')->name('reportModalView');

    Route::get('/', 'downoladExcel')->name('report');

    Route::get('view', 'reportview')->name('report.view');

    Route::get('guard/{id}', 'getSupervisorGuard')->name('report.guard');

    Route::get('supervisor/{id}', 'getSupervisor')->name('report.supervisor');

    Route::get('client-site/{id}', 'getClientSite')->name('clientSite');

    Route::get('show', 'showReport')->name('showReport');
});

Route::prefix('incidence')->controller(IncidenceController::class)->group(function () {

    // Main Incidence
    Route::get('/', 'index')->name('incidence');

    Route::get('get/{site_id}', 'getIncidence')->name('getincidence');

    Route::get('action/{site_id}/{incidence_id}', 'incidenceActionTaken')->name('incidenceActionTaken');

    Route::get('resolve/{id}', 'incidenceResolve')->name('incidence.resolve');
    Route::get('ignore/{id}', 'incidenceIgnore')->name('incidence.ignore');
    Route::get('escalate/{id}', 'incidenceEscalate')->name('incidence.escalate');

    Route::get('list/{status}/{date}', 'incidences')->name('incidences');

    // Export & Reports
    Route::get('export', 'incidenceExport')->name('incidence.incidenceExport');
    Route::get('fetch', 'fetchIncidences')->name('incidence.fetchincidences');
    Route::get('attend-report-site', 'attendReportWithSite')->name('incidence.attendReportWithSite');

    // Incidence Type
    Route::prefix('type')->group(function () {

            Route::get('/', 'incidenceType')->name('incidence.type');

            Route::get('create', 'incidenceTypeCreate')->name('incidenceType.create');
            Route::post('create', 'incidenceTypeCreateAction')->name('incidenceType.createaction');

            Route::get('edit/{id}', 'editIncidenceType')->name('incidenceType.edit');
            Route::post('edit', 'editIncidenceTypeAction')->name('incidenceType.editaction');

            Route::get('delete/{id}', 'deleteIncidenceType')->name('incidenceType.delete');

            Route::get('get/{id}', 'getIncidenceType')->name('getIncidence.type');
        }
        );

        // Incidence Sub Type
        Route::prefix('subtype')->group(function () {

            Route::get('create', 'incidenceSubTypeCreate')->name('incidenceSubType.create');
            Route::post('create', 'incidenceSubTypeCreateAction')->name('incidenceSubType.createaction');

            Route::get('delete/{type_id}/{id}', 'deleteIncidenceSubType')->name('incidenceSubType.delete');
        }
        );
    });


// Quick View Modal Data
Route::get('/api/kpi-quick-view', [App\Http\Controllers\ForestReportConfigController::class , 'getKpiQuickView'])->name('kpi.quickview');

// Detailed Data Table View
Route::get('/reports/detailed', [App\Http\Controllers\ForestReportConfigController::class , 'detailedDataTable'])->name('reports.detailed');