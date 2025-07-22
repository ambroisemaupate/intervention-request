ARG UID=1000
ARG GID=${UID}

ARG PHP_VERSION=8.4.10

#######
# PHP #
#######

FROM php:${PHP_VERSION}-fpm-bookworm AS php

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

ARG COMPOSER_VERSION=2.8.2

ENV IR_GC_PROBABILITY=400
ENV IR_GC_TTL=604800
# 1 year
ENV IR_RESPONSE_TTL=31557600
ENV IR_USE_FILECHECKSUM=0
ENV IR_USE_PASSTHROUGH_CACHE=1
ENV IR_DRIVER="gd"
ENV IR_CACHE_PATH=/app/public/assets
ENV IR_IGNORE_PATH=/assets
ENV IR_DEFAULT_QUALITY=80
ENV IR_IMAGES_PATH=/app/public/images
ENV IR_JPEGOPTIM_PATH=/usr/bin/jpegoptim
ENV IR_PNGQUANT_PATH=/usr/bin/pngquant

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

COPY --link docker/crontab.txt /crontab.txt

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
# Packages - System
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    less \
    nginx \
    cron \
    pngquant \
    supervisor \
    jpegoptim \
    sudo
rm -rf /var/lib/apt/lists/*

# User
addgroup --gid ${UID} php
adduser --home /home/php --shell /bin/bash --uid ${GID} --gecos php --ingroup php --disabled-password php
echo "php ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/php

# App
install --verbose --owner php --group php --mode 0755 --directory /app

/usr/bin/crontab -u php /crontab.txt
chown -R php:php /app

# Php extensions
curl -sSLf  https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    --output /usr/local/bin/install-php-extensions
chmod +x /usr/local/bin/install-php-extensions
install-php-extensions \
    @composer-${COMPOSER_VERSION} \
    apcu \
    exif \
    gd \
    imagick \
    opcache \
    zip

install --verbose --owner php --group php --mode 0755 --directory /var/lib/nginx --directory /var/log/nginx
chown -R php:php /var/log/nginx
EOF

COPY --link docker/etc/php/zz-docker.conf              /usr/local/etc/php-fpm.d/zz-docker.conf
COPY --link docker/etc/nginx/nginx.conf                /etc/nginx/nginx.conf
COPY --link docker/etc/nginx/mime.types                /etc/nginx/mime.types
COPY --link docker/etc/nginx/conf.d/_gzip.conf         /etc/nginx/conf.d/_gzip.conf
COPY --link docker/etc/nginx/conf.d/_security.conf     /etc/nginx/conf.d/_security.conf
COPY --link docker/etc/nginx/conf.d/default.conf       /etc/nginx/conf.d/default.conf
COPY --link docker/etc/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY --link docker/etc/supervisor/conf.d/services.conf /etc/supervisor/conf.d/services.conf

COPY --link --chmod=755 docker/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint

WORKDIR /app


##################
# PHP Production #
##################

FROM php AS php-prod

ARG UID
ARG GID

ENV IR_DEBUG=0

RUN ln -sf ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini
COPY --link docker/etc/php/conf.d/strict.ini ${PHP_INI_DIR}/conf.d/zz-strict.ini
COPY --link docker/etc/php/conf.d/php.prod.ini ${PHP_INI_DIR}/conf.d/zz-app.ini

# Composer
COPY --link --chown=${UID}:${GID} composer.* ./

RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link --chown=${UID}:${GID} . .

RUN composer dump-autoload --classmap-authoritative --no-dev

# Declare volumes with the correct user
USER php

VOLUME /app/public/images \
       /app/public/assets

USER root

CMD [ "supervisord", "-n",  "-u", "root", "-c", "/etc/supervisor/supervisord.conf" ]

EXPOSE 80

###########
# PHP Dev #
###########

FROM php AS php-dev

ENV IR_DEBUG=1

RUN ln -sf ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini
COPY --link docker/etc/php/conf.d/strict.ini ${PHP_INI_DIR}/conf.d/zz-strict.ini

# Declare volumes with the correct user
USER php

VOLUME /app

USER root

CMD [ "supervisord", "-n",  "-u", "root", "-c", "/etc/supervisor/supervisord.conf" ]

EXPOSE 80


##############
# FrankenPHP #
##############

FROM dunglas/frankenphp:php${PHP_VERSION}-bookworm AS frankenphp

LABEL org.opencontainers.image.authors="ambroise@rezo-zero.com"

ARG UID
ARG GID

ARG COMPOSER_VERSION=2.8.2

ENV IR_GC_PROBABILITY=400
ENV IR_GC_TTL=604800
# 1 year
ENV IR_RESPONSE_TTL=31557600
ENV IR_USE_FILECHECKSUM=0
ENV IR_USE_PASSTHROUGH_CACHE=1
ENV IR_DRIVER="gd"
ENV IR_CACHE_PATH=/app/public/assets
ENV IR_IGNORE_PATH=/assets
ENV IR_DEFAULT_QUALITY=80
ENV IR_IMAGES_PATH=/app/public/images
ENV IR_JPEGOPTIM_PATH=/usr/bin/jpegoptim
ENV IR_PNGQUANT_PATH=/usr/bin/pngquant

SHELL ["/bin/bash", "-e", "-o", "pipefail", "-c"]

COPY --link docker/crontab.txt /crontab.txt

RUN <<EOF
apt-get --quiet update
apt-get --quiet --yes --purge --autoremove upgrade
# Packages - System
apt-get --quiet --yes --no-install-recommends --verbose-versions install \
    less \
    nginx \
    cron \
    pngquant \
    supervisor \
    jpegoptim \
    sudo
rm -rf /var/lib/apt/lists/*

# User
addgroup --gid ${UID} php
adduser --home /home/php --shell /bin/bash --uid ${GID} --gecos php --ingroup php --disabled-password php
echo "php ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/php

# App
install --verbose --owner php --group php --mode 0755 --directory /app

/usr/bin/crontab -u php /crontab.txt
chown -R php:php /app

# Php extensions
curl -sSLf  https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    --output /usr/local/bin/install-php-extensions
chmod +x /usr/local/bin/install-php-extensions
install-php-extensions \
    @composer-${COMPOSER_VERSION} \
    apcu \
    exif \
    gd \
    imagick \
    opcache \
    zip

setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp

chown --recursive ${UID}:${GID} /data/caddy /config/caddy
EOF

WORKDIR /app

#######################
# Php - franken - Dev #
#######################

FROM frankenphp AS frankenphp-dev

ENV IR_DEBUG=1
ENV XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

USER php

VOLUME /app

########################
# Php - franken - Prod #
########################

FROM frankenphp AS frankenphp-prod

ARG UID
ARG GID

ENV IR_DEBUG=0

RUN mv ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

# Composer
COPY --link --chown=${UID}:${GID} composer.* ./

RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link --chown=${UID}:${GID} . .

RUN composer dump-autoload --classmap-authoritative --no-dev

# Declare volumes with the correct user
USER php

VOLUME /app/public/images \
       /app/public/assets
