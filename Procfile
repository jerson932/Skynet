web: bash -lc 'mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache && chmod -R a+rw storage bootstrap/cache && { [ -L public/storage ] || php artisan storage:link || true; } && php -S 0.0.0.0:${PORT} -t public public/index.php'
release: |
  mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache
  chmod -R a+rw storage bootstrap/cache
  php artisan optimize:clear
  php artisan key:generate --force
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:clear
  php artisan view:cache
  php artisan event:cache