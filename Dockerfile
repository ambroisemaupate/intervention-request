FROM php:8.3-fpm-alpine
MAINTAINER Ambroise Maupate <ambroise@rezo-zero.com>
LABEL org.opencontainers.image.authors="Ambroise Maupate <ambroise@rezo-zero.com>"

ARG USER_UID=1000
ENV IR_DEFAULT_QUALITY 90
ENV IR_DRIVER "gd"
ARG IMAGICK_VERSION=3.7.0

# Add PHP extensions for image manipulation
RUN apk --no-cache update \
    && apk --no-cache upgrade \
    && apk add --no-cache --virtual \
        .build-deps \
        $PHPIZE_DEPS \
        autoconf \
        g++ \
        gcc \
        git \
        imagemagick-dev \
        libtool \
        tar \
    && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" NPROC=$(getconf _NPROCESSORS_ONLN) \
    && apk add --no-cache \
        aspell-dev \
        bash \
        ca-certificates \
        curl \
        dcron \
        freetds-dev \
        freetype \
        freetype-dev \
        imagemagick \
        imagemagick-libs \
        jpegoptim \
        libavif \
        libavif-dev \
        libjpeg-turbo \
        libjpeg-turbo-dev \
        libpng \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        pngquant \
        shadow \
        sudo \
        supervisor \
        zip \
    && apk add --no-cache \
        nginx \
    && phpModules=" \
        apcu \
        exif \
        gd \
        opcache \
        zip \
    " \
    && docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp --with-avif \
    # Install APCU
    # @see https://github.com/docker-library/php/issues/1029
    && mkdir -p /usr/src/php/ext/apcu  \
    && curl -fsSL https://pecl.php.net/get/apcu/stable | tar xvz -C "/usr/src/php/ext/apcu" --strip 1 \
    && docker-php-ext-install -j${NPROC} $phpModules \
    && docker-php-ext-enable --ini-name 20-apcu.ini apcu \
# Imagick is installed from the archive because regular installation fails
# See: https://github.com/Imagick/imagick/issues/643#issuecomment-1834361716 \
    && cd /tmp \
    && curl -L -o /tmp/imagick.tar.gz https://github.com/Imagick/imagick/archive/refs/tags/${IMAGICK_VERSION}.tar.gz \
    && tar --strip-components=1 -xf /tmp/imagick.tar.gz \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && echo "extension=imagick.so" > /usr/local/etc/php/conf.d/20-imagick.ini \
    && rm -rf /tmp/* \
# <<< End of Imagick installation
    && apk del --no-cache gcc g++ git freetype-dev libpng-dev libjpeg-turbo-dev .build-deps $PHPIZE_DEPS

ADD docker/etc/php/php.ini /usr/local/etc/php/php.ini
ADD docker/etc/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
ADD docker/etc/nginx /etc/nginx

ADD docker/etc/supervisord.ini /etc/supervisor.d/services.ini
ADD docker/etc/before_launch.ini /etc/supervisor.d/before_launch.ini
ADD docker/etc/before_launch.sh /before_launch.sh

COPY --chown=www-data:www-data . /var/www/html/
COPY docker/crontab.txt /crontab.txt

RUN apk add --no-cache shadow \
    && curl -sS https://getcomposer.org/installer | \
       php -- --install-dir=/usr/bin/ --filename=composer \
    && composer install --no-plugins --no-scripts --prefer-dist \
    # Need to autoload dev dependencies for AWS S3 filesystem
    && composer dump-autoload --classmap-authoritative --apcu --dev \
    && usermod -u ${USER_UID} www-data \
    && groupmod -g ${USER_UID} www-data \
    && mkdir -p /var/www/html/web/assets \
    && mkdir -p /var/www/html/web/images \
    && chmod +x /before_launch.sh \
    && /usr/bin/crontab -u www-data /crontab.txt

EXPOSE 80
VOLUME /var/www/html/web/images /var/www/html/web/assets
ENTRYPOINT exec /usr/bin/supervisord -n -c /etc/supervisord.conf
