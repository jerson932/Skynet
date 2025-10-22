<?php

return [
    'paths' => [
        resource_path('views'),
    ],

    // Usar env si existe; si no, la ruta por defecto en storage
    'compiled' => env('VIEW_COMPILED_PATH') ?: storage_path('framework/views'),
];