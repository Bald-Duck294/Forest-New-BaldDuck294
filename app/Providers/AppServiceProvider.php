<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            try {
                // 1. Ensure the dev user exists in session
                if (!session()->has('user')) {
                    session()->put('user', (object)[
                        "company_id" => 56,
                        "id" => 1,
                        "isActive" => 1,
                        "role_id" => 1,
                        "name" => "Dev Admin",
                        "email" => "dev@forestfix.com", // Added email to prevent next error
                        "profile_photo" => null,
                        "profile_pic"   => null
                    ]);
                }

                // 2. FETCH THE USER FROM SESSION
                $user = session('user');

                $provider = new class {
                    use FilterDataTrait;
                };

                $filterData = $provider->filterData();

                // 3. SHARE BOTH THE FILTERS AND THE USER OBJECT
                $view->with($filterData);
                $view->with('user', $user); // This line fixes the "Attempt to read property name on null"
                $view->with('isSimulating', true); // Added this since your sidebar checks for it

            } catch (\Exception $e) {
                \Log::error('View Composer Error: ' . $e->getMessage());
                $view->with([
                    'ranges' => collect(),
                    'beats' => collect(),
                    'users' => collect(),
                    'user' => session('user'), // Still try to pass the user even on error
                ]);
            }
        });

        Blade::directive('formatName', function ($expression) {
            return "<?php echo App\Helpers\FormatHelper::formatName($expression); ?>";
        });
    }
}
