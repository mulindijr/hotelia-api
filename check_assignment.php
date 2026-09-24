<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'mulindijrn@gmail.com')->first();
$roles = \DB::table('model_has_roles')->where('model_id', $u->id)->get();
echo "Roles assignment: " . json_encode($roles) . "\n";

echo "Has Role checking with hotel_id=null: " . ($u->hasRole('super_admin') ? 'Yes' : 'No') . "\n";
