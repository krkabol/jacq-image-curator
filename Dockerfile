#https://github.com/dockette/web/tree/master/debian-php-83
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"
FROM dockette/web:php-83
RUN apt update && apt dist-upgrade -y && \
    apt install -y --no-install-recommends \
        imagemagick \
        zbar-tools \
        php8.3-imagick && \
        apt-get autoclean -y && \
        apt-get remove -y wget && \
        apt-get autoremove -y && \
        rm -rf /var/lib/apt/lists/* /var/lib/log/* /tmp/* /var/tmp/*

# disable logs
RUN rm /var/log/nginx/access.log && \
    rm /var/log/nginx/error.log

COPY htdocs /srv/
