<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$classes = DB::table('classes')->get();
foreach($classes as $c) {
    echo "Class: " . $c->class_name . " (ID: " . $c->classID . ")\n";
    $subclasses = DB::table('subclasses')->where('classID', $c->classID)->get();
    foreach($subclasses as $s) {
        echo "  Subclass: " . $s->subclass_name . " (ID: " . $s->subclassID . ", Status: " . $s->status . ")\n";
    }
}
