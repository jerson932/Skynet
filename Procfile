web: php -S 0.0.0.0:${PORT} -t public public/index.php
release: bash -lc 'mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
&& chmod -R 777 storage bootstrap/cache \
&& php artisan optimize:clear \
&& php artisan key:generate --force \
&& php artisan storage:link || true \
&& php artisan migrate --force \
&& php artisan config:cache \
&& php artisan route:cache \
&& php artisan view:clear \
&& php artisan view:cache \
&& php artisan event:cache'