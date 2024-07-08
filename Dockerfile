#https://github.com/dockette/web/tree/master/debian-php-83
FROM dockette/web:php-83
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"
RUN apt-get update && apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends \
        imagemagick \
        zbar-tools \
        php8.3-imagick && \
        apt-get autoclean -y && \
        apt-get remove -y wget && \
        apt-get autoremove -y && \
        rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/*

# disable logs
RUN rm /var/log/nginx/access.log && \
    rm /var/log/nginx/error.log && \
    rm -rf /srv/www

COPY htdocs /srv/

RUN  chmod -R 777 /srv/log /srv/temp

