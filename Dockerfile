FROM ghcr.io/krkabol/curator_base:main@sha256:a894e5f5f0399e3a06ad734b87dffc8638ab46a848e7ad36ce4abdfa2f1a2f66
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"

# devoted for Kubernetes, where the app has to be copied into final destination (/app) after the container starts
COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
