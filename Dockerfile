FROM ghcr.io/krkabol/curator_base:main@sha256:726f9a51052ce93358cf2ad398c69b25112c7e2df6c5aaa4d9e1c11a33a27880
LABEL org.opencontainers.image.source=https://github.com/krkabol/jacq-image-curator
LABEL org.opencontainers.image.description="Image processing for JACQ herabrium service"

# devoted for Kubernetes, where the app has to be copied into final destination (/srv) after the container starts
COPY htdocs /app

## use in case you want to run in docker on local machine
#COPY htdocs /var/www/html
