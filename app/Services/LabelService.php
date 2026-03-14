<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LabelService
{
    public static function getLabels($companyId)
    {
        $cacheKey = "company_{$companyId}_labels";

        return Cache::rememberForever($cacheKey, function () use ($companyId) {

            return DB::table('field_masters')
                ->leftJoin('company_field_labels', function ($join) use ($companyId) {

                    $join->on('field_masters.field_key', '=', 'company_field_labels.field_key')
                        ->where('company_field_labels.company_id', $companyId);

                })
                ->select(
                    'field_masters.field_key',
                    DB::raw('COALESCE(company_field_labels.custom_label, field_masters.default_label) as label')
                )
                ->pluck('label', 'field_key')
                ->toArray();

        });
    }

    public static function clearCache($companyId)
    {
        Cache::forget("company_{$companyId}_labels");
    }
}