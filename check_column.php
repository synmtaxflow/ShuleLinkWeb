<?php
use Illuminate\Support\Facades\DB;
$describe = DB::select('DESCRIBE results');
foreach($describe as $field) {
    if($field->Field == 'status') {
        echo "results.status: " . $field->Type . "\n";
    }
}
