[![Build Status](https://github.com/krkabol/jacq-image-curator/actions/workflows/publish.yml/badge.svg)](https://github.com/krkabol/jacq-image-curator/actions?query=workflow%3ABuild+branch%3Amain)
[![GitHub All Releases](https://img.shields.io/github/downloads/krkabol/jacq-image-curator/total)](https://github.com/krkabol/jacq-image-curator/releases)
[![codecov](https://codecov.io/gh/krkabol/jacq-image-curator/branch/main/graph/badge.svg?token=YOUR_TOKEN)](https://codecov.io/gh/krkabol/jacq-image-curator)

[//]: # (![PHPStan]&#40;https://img.shields.io/badge/style-level%207-brightgreen.svg?&label=phpstan&#41;)


# jacq-image-curator
The web application Curator handles primary data management after uploaded from herbaria in the Czech Republic. A develop version can be found at [https://herbarium.dyn.cloud.e-infra.cz/](https://herbarium.dyn.cloud.e-infra.cz/).

## Key points
* Archive Master File (scanned photo in the highest quality) is available through proxy of this app. There is no access restriction, the S3 ACL is not turned on.
* Bucket versioning is not turned on.
* IIIF manifest only in v2

## Installation
Use the docker image from GitHub Container Registry, but take a note that the container is fitted to Kubernetes, for docker-compose approach need to rebuild.

## DevOps
* https://github.com/slevomat/coding-standard/tree/master?tab=readme-ov-file#suppressing-sniffs-locally

