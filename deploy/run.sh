echo "current path is ${PWD}, starting execute run.sh";

echo "files in current folder:";
ls -la;

echo "running composer";
composer dump-autoload;
composer install --working-dir=/app;

bash /app/deploy/wait_for.sh mysql_db:3306 -t 0 -s --;
echo "database is ready";

echo "setting files permissions";
chown -R www-data:www-data /app;

echo "starting supervisor";
service supervisor start;
echo "supervisor booted";

echo "running cron";
service cron start;

echo "cron running";
crontab -u www-data /app/deploy/cron/www-data;

echo "running deploy:init command";
php /app/artisan deploy:init;

php-fpm --nodaemonize --fpm-config /usr/local/etc/php-fpm.d/php-izan-back.conf;
