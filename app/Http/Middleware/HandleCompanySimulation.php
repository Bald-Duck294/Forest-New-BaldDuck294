<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\CompanyDetails;

class HandleCompanySimulation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = session('user');

        // Check if the user is a Global Admin (role 8)
        if ($user && isset($user->role_id) && $user->role_id == 8) {
            $simulatedCompanyId = session('simulated_company_id');
            $currentCompanyInSession = session('company');
            $currentCompanyIdInSession = $currentCompanyInSession ? $currentCompanyInSession->id : null;

            $route = $request->route();
            $isGlobalRoute = $route && (str_starts_with($route->getName(), 'global.') || str_contains($request->path(), 'global-dashboard') || str_contains($request->path(), 'global/'));

            if ($simulatedCompanyId && !$isGlobalRoute) {
                // We are simulating a company and NOT on a global route.
                // Ensure features and company details match the simulated company.
                if ($currentCompanyIdInSession != $simulatedCompanyId) {
                    $company = CompanyDetails::find($simulatedCompanyId);
                    if ($company) {
                        // Extract features
                        $features = [];
                        $incCheck = json_decode($company->features, true);
                        if ($incCheck) {
                            foreach ($incCheck as $checked) {
                                if (isset($checked['checked']) && $checked['checked']) {
                                    $features[] = $checked['value'];
                                }
                            }
                        }
                        session()->put('features', $features);
                        session()->put('company', $company);

                        // Extract dashboard checklist
                        $dashboardChecklist = [];
                        $incChecklist = json_decode($company->dashboardChecklist, true);
                        if ($incChecklist) {
                            foreach ($incChecklist as $checked) {
                                if (isset($checked['checked']) && $checked['checked']) {
                                    $dashboardChecklist[] = $checked['value'];
                                }
                            }
                        }
                        session()->put('dashboardChecklist', $dashboardChecklist);
                    }
                }
            } elseif ($isGlobalRoute) {
                // If on a global route, we might want to clear company session data to prevent confusion,
                // BUT we keep the simulated_company_id so we can return to it.
                if ($currentCompanyIdInSession) {
                    session()->forget(['company', 'features', 'dashboardChecklist']);
                }
            }
        }

        return $next($request);
    }
}
