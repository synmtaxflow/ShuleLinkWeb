<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    public function verify()
    {
        try {
            $connection = config('database.default');
            $database = config("database.connections.{$connection}.database");
            
            $tables = [];
            if ($connection === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
            } else {
                $tables = DB::select("SHOW TABLES");
            }
            
            $tableDetails = [];
            foreach ($tables as $table) {
                $tableName = is_object($table) ? $table->name : (is_array($table) ? array_values($table)[0] : $table);
                
                if (Schema::hasTable($tableName)) {
                    $count = DB::table($tableName)->count();
                    $tableDetails[] = [
                        'name' => $tableName,
                        'count' => $count,
                        'exists' => true
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'connection' => $connection,
                'database' => $database,
                'database_exists' => file_exists($database),
                'tables' => $tableDetails,
                'total_tables' => count($tableDetails)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}







