FROM ghcr.io/krkabol/php-fpm-noroot-socket:main
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"
USER root
RUN apt-get update && apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends \
        imagemagick \
        libgraphicsmagick1-dev \
        libmagickwand-dev \
        libpq-dev \
        zbar-tools && \
        apt-get autoclean -y && \
        apt-get remove -y wget && \
        apt-get autoremove -y && \
        rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/*

RUN  pecl install imagick-3.7.0 && \
     docker-php-ext-enable imagick && \
     docker-php-ext-install pdo

RUN  docker-php-ext-install pdo_pgsql
RUN  docker-php-ext-install pgsql
RUN  docker-php-ext-install opcache

#increase Imagick limits
COPY ./policy.xml /etc/ImageMagick-v6/policy.xml
USER www

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY htdocs /app

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
