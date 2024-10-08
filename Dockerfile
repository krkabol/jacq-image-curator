FROM ghcr.io/krkabol/curator_base:main@sha256:0c4c73a6f8dddd73a2bd4224d6ce4f18d4a96ef80b2b53d6ab4d2d318bfc3e3d
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"

# devoted for Kubernetes, where the app has to be copied into final destination (/app) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
