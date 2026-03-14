<?php

use App\Services\LabelService;
use Illuminate\Support\Facades\Auth;

if (!function_exists('get_label')) {

    function get_label($key, $fallback = '')
    {
        if (!Auth::check()) {
            return $fallback;
        }

        $companyId = Auth::user()->company_id;

        $labels = LabelService::getLabels($companyId);

        return $labels[$key] ?? $fallback;
    }
}