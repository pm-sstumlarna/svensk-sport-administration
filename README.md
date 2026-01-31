# Svensk Sport Administration

## Projektets Syfte

Detta är ett projekt som jag påbörjar för att fler skall kunna bidra till att göra svensk idrott ännu bättre. Det finns
många ledare, tränare och föräldrar som engagerar sig ideellt så då känns det som en självklarhet att även bjuda in dem
som arbetar med mjukvara. Tillsammans kan vi göra svensk idrott ännu bättre så att fler barn och ungdomar får möjlighet
att utöva idrott.

## Bidra till projektet

## Test- och utvecklingsmiljö
För att bygga en container med utvecklingsmiljön konfigurerad kan du exekvera följande script:

```bash
$ ./scripts/podman-build.sh
```
För att hämta de bibliotek som behövs kan du köra följande kommando:

```bash
$ ./script/poddman-run.sh composer install
```

Testerna kan köras med kommandot:

```bash
$ ./script/podman-run.sh composer test
```

## Dokumentation