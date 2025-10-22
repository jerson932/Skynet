<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Visit;
use App\Exports\VisitsExport;

$visits = Visit::with(['client','supervisor','tecnico'])->limit(20)->get();
$export = new VisitsExport($visits);
$stream = $export->toXlsxStream();
$path = __DIR__ . '/storage/app/visits_debug.xlsx';
ob_start();
$stream();
$content = ob_get_clean();
file_put_contents($path, $content);
echo "Wrote: {$path}\n";