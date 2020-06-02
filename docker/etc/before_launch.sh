# Fix volume permissions
/bin/chown -R www-data:www-data /var/www/html/web/images;
/bin/chown -R www-data:www-data /var/www/html/web/assets;
/bin/chown -R www-data:www-data /var/www/html/web/index.php;

printenv | sed 's/^\(.*\)$/export \1/g' | grep -E "^export IR_" > /var/www/html/project_env.sh
/bin/chown -R www-data:www-data /var/www/html/project_env.sh;
/bin/chmod u+x /var/www/html/project_env.sh;
