<?php

namespace App;
use Carbon\Carbon;
use Carbon\CarbonInterval;

use Illuminate\Database\Eloquent\Model;

class GetDays extends Model
{

    public static function getDays($day, $startmonth, $startyear, $endmonth, $endyear)
    {
        return new \DatePeriod(
            Carbon::parse("first " . $day . " of " . $startmonth . " " . $startyear),
            CarbonInterval::week(),
            Carbon::parse("last " . $day . " of " . $endmonth . " " . $endyear)
        );
    }
}
