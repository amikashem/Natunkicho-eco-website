<?php

declare(strict_types=1);

namespace NKRecruitment\Search\Helpers;

use NKRecruitment\Database\DatabaseManager;

if (!defined('ABSPATH')) {
    exit;
}

class SearchHelper
{
    /**
     * Gets a list of unique locations (cities/countries) currently in the Jobs database.
     */
    public static function getActiveJobLocations(): array
    {
        global $wpdb;
        $table = DatabaseManager::table('jobs');
        
        $sql = "SELECT DISTINCT location_city, location_country 
                FROM {$table} 
                WHERE status = 'published' 
                AND location_city != '' 
                ORDER BY location_city ASC";
                
        $results = $wpdb->get_results($sql);
        
        $locations = [];
        foreach ($results as $row) {
            $loc = trim($row->location_city . ($row->location_country ? ', ' . $row->location_country : ''));
            if ($loc && !in_array($loc, $locations)) {
                $locations[] = $loc;
            }
        }
        return $locations;
    }

    /**
     * Gets a list of unique Job Types (Full-Time, Part-Time, etc.)
     */
    public static function getActiveJobTypes(): array
    {
        global $wpdb;
        $table = DatabaseManager::table('jobs');
        
        return $wpdb->get_col("SELECT DISTINCT job_type FROM {$table} WHERE status = 'published' AND job_type != '' ORDER BY job_type ASC");
    }

    /**
     * Gets a list of unique Industries currently in the Companies database.
     */
    public static function getActiveIndustries(): array
    {
        global $wpdb;
        $table = DatabaseManager::table('companies');
        
        return $wpdb->get_col("SELECT DISTINCT industry FROM {$table} WHERE status = 'active' AND industry != '' ORDER BY industry ASC");
    }
}