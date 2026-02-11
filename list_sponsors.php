<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sponsors = DB::table('sponsors')->get();
foreach($sponsors as $s) {
    echo "ID: " . $s->sponsorID . " Name: " . $s->sponsor_name . "\n";
}
