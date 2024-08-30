# jacq-image-curator
The web application Curator handles primary data management after uploaded from herbaria in the Czech Republic. A develop version can be found at [https://herbarium.dyn.cloud.e-infra.cz/](https://herbarium.dyn.cloud.e-infra.cz/).

## Instalation
Use the docker image from GitHub Container Registry, but take a note that the container is fitted to Kubernetes, for docker-compose approach need to be rebuilded.

## TODO
* nette user, db table
* vylistovat obsah new, jen tif, range velikosti souboru
* kurátor odbouchne "vše ke kontrole" (zatím bez možnosti vybrat subset)
* dojde k přesunu do pracovního bucketu, uniátní jméno a záznam do db
* stavy budou: ko kontrole, kontrolovaný, chyba kontroly (to by měl umět vráti do new) ke zveřejnění, zvěřejněný, skrytý
* kontrola beží jako k8s job, přečte barcode - že správný herbář a číslo
* vylistovat "ke zeveřejnění"
* kurátor odbouchne "vše zveřejnit"
*


