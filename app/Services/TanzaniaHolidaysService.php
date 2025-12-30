<?php

namespace App\Services;

use Carbon\Carbon;

class TanzaniaHolidaysService
{
    /**
     * Get all Tanzania public holidays for a given year
     * Returns array of holidays with name, date, and type
     */
    public static function getHolidaysForYear($year)
    {
        $holidays = [];

        // Fixed Date Holidays
        $holidays[] = [
            'name' => 'New Year\'s Day',
            'date' => Carbon::create($year, 1, 1),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Zanzibar Revolution Day',
            'date' => Carbon::create($year, 1, 12),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Karume Day',
            'date' => Carbon::create($year, 4, 7),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Union Day',
            'date' => Carbon::create($year, 4, 26),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Labour Day',
            'date' => Carbon::create($year, 5, 1),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Saba Saba Day (Peasants\' Day)',
            'date' => Carbon::create($year, 7, 7),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Nane Nane Day (Farmers\' Day)',
            'date' => Carbon::create($year, 8, 8),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Nyerere Day',
            'date' => Carbon::create($year, 10, 14),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Independence Day (Uhuru Day)',
            'date' => Carbon::create($year, 12, 9),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Christmas Day',
            'date' => Carbon::create($year, 12, 25),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Boxing Day',
            'date' => Carbon::create($year, 12, 26),
            'type' => 'Public Holiday'
        ];

        // Easter-based holidays (variable dates)
        $easter = self::calculateEaster($year);
        
        $holidays[] = [
            'name' => 'Good Friday',
            'date' => $easter->copy()->subDays(2),
            'type' => 'Public Holiday'
        ];

        $holidays[] = [
            'name' => 'Easter Monday',
            'date' => $easter->copy()->addDay(),
            'type' => 'Public Holiday'
        ];

        // Islamic holidays (approximate - based on lunar calendar)
        // Note: These are approximations. Actual dates vary by moon sighting
        $eidAlFitr = self::calculateEidAlFitr($year);
        if ($eidAlFitr) {
            $holidays[] = [
                'name' => 'Eid al-Fitr',
                'date' => $eidAlFitr,
                'type' => 'Public Holiday'
            ];
        }

        $eidAlAdha = self::calculateEidAlAdha($year);
        if ($eidAlAdha) {
            $holidays[] = [
                'name' => 'Eid al-Adha',
                'date' => $eidAlAdha,
                'type' => 'Public Holiday'
            ];
        }

        // Maulid Day (Prophet's Birthday) - approximate
        $maulid = self::calculateMaulid($year);
        if ($maulid) {
            $holidays[] = [
                'name' => 'Maulid Day',
                'date' => $maulid,
                'type' => 'Public Holiday'
            ];
        }

        // Sort by date
        usort($holidays, function($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });

        return $holidays;
    }

    /**
     * Calculate Easter date using algorithm
     */
    private static function calculateEaster($year)
    {
        // Anonymous Gregorian algorithm
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = floor(($a + 11 * $h + 22 * $l) / 451);
        $month = floor(($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day);
    }

    /**
     * Calculate Eid al-Fitr (approximate - end of Ramadan)
     * Using approximate dates based on common patterns
     */
    private static function calculateEidAlFitr($year)
    {
        // Approximate: Eid al-Fitr usually falls in April or May
        // This is a rough estimate - actual date depends on moon sighting
        // For 2025: approximately April 1-2
        // Adjusting based on year
        $baseDate = Carbon::create($year, 4, 1);
        
        // Add year offset (Islamic calendar shifts ~11 days earlier each year)
        $yearOffset = ($year - 2025) * 11;
        $eidDate = $baseDate->copy()->subDays($yearOffset % 30);
        
        return $eidDate;
    }

    /**
     * Calculate Eid al-Adha (approximate - Hajj day)
     * Usually about 70 days after Eid al-Fitr
     */
    private static function calculateEidAlAdha($year)
    {
        $eidFitr = self::calculateEidAlFitr($year);
        // Eid al-Adha is approximately 70 days after Eid al-Fitr
        return $eidFitr->copy()->addDays(70);
    }

    /**
     * Calculate Maulid (Prophet's Birthday) - approximate
     * Usually in September or October
     */
    private static function calculateMaulid($year)
    {
        // Approximate: Usually in September
        $baseDate = Carbon::create($year, 9, 15);
        
        // Add year offset
        $yearOffset = ($year - 2025) * 11;
        $maulidDate = $baseDate->copy()->subDays($yearOffset % 30);
        
        return $maulidDate;
    }
}

