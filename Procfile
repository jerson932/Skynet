web: php -S 0.0.0.0:${PORT} -t public public/index.php
release: php -r "foreach (['storage/framework','storage/framework/cache','storage/framework/views','storage/framework/sessions','storage/logs','bootstrap/cache'] as $d){ if(!is_dir($d)) mkdir($d,0777,true); }" \
 && chmod -R a+rw storage bootstrap/cache \
 && php artisan optimize:clear \
 && php artisan key:generate --force \
 && php artisan storage:link || true \
 && php artisan migrate --force \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:clear \
 && php artisan view:cache \
 && php artisan event:cache