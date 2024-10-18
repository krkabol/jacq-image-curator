FROM ghcr.io/krkabol/curator_base:main@sha256:5e4cf7b8f8f0b315735142d0b58196ddb659ed2cdafdd1740930a379869c092a
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"

# devoted for Kubernetes, where the app has to be copied into final destination (/app) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
