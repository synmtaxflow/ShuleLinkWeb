<?php
use Illuminate\Support\Facades\DB;
$tables = DB::select('SHOW TABLES');
foreach($tables as $table) {
    $tableArray = (array)$table;
    echo reset($tableArray) . "\n";
}
