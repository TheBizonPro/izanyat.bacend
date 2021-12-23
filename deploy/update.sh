echo "running composer"
composer install;
echo "running deploy:init command";
php /app/artisan deploy:init;
echo "setting files permissions";
chown -R www-data:www-data /app;
