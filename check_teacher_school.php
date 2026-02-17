<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Subclass 20 is "Form Three A".
$subclass = DB::table('subclasses')->where('subclassID', 20)->first();
if ($subclass) {
    echo "Subclass 20 - teacherID: " . $subclass->teacherID . "\n";
    $teacher = DB::table('teachers')->where('id', $subclass->teacherID)->first();
    if ($teacher) {
        echo "Teacher schoolID: " . $teacher->schoolID . "\n";
    } else {
        echo "Teacher with ID " . $subclass->teacherID . " not found in teachers table\n";
    }
    
    $class = DB::table('classes')->where('classID', $subclass->classID)->first();
    if ($class) {
        echo "Class schoolID: " . $class->schoolID . "\n";
    }
} else {
    echo "Subclass 20 not found\n";
}
