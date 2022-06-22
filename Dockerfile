FROM roadiz/php81-nginx-alpine:latest
MAINTAINER Ambroise Maupate <ambroise@rezo-zero.com>
ARG USER_UID=1000

ADD docker/etc /etc
ADD docker/etc/before_launch.sh /before_launch.sh
COPY --chown=www-data:www-data . /var/www/html/
COPY docker/crontab.txt /crontab.txt

RUN apk add --no-cache shadow \
    && curl -sS https://getcomposer.org/installer | \
       php -- --install-dir=/usr/bin/ --filename=composer \
    && composer install --no-plugins --no-scripts --prefer-dist \
    && composer dump-autoload --optimize --apcu \
    && usermod -u ${USER_UID} www-data \
    && groupmod -g ${USER_UID} www-data \
    && mkdir -p /var/www/html/web/assets \
    && mkdir -p /var/www/html/web/images \
    && chmod +x /before_launch.sh \
    && /usr/bin/crontab -u www-data /crontab.txt

VOLUME /var/www/html/web/images /var/www/html/web/assets

ENTRYPOINT exec /usr/bin/supervisord -n -c /etc/supervisord.conf
