FROM ghcr.io/krkabol/curator_base:main
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY htdocs /app

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
