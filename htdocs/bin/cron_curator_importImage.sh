#!/usr/bin/env bash
./console curator:importImage

#  for testing on local machine:
#  docker run --network host -v ./htdocs:/app -w /app/bin ghcr.io/krkabol/curator_base:main ./cron_curator_importImage.sh
