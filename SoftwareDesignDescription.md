# Designdokumentation för mjukvara - Svensk Sport Administration

## 1. Introduktion

Detta dokument beskriver designen för Svensk Sport Administration som tillhandahåller ett REST-baserat API för hantering
av klubbens data.

## 2. Systemarkitektur

Svensk Sport Administration är mjukvara som gör det enkelt att hantera klubbens data så som medlemsregister, kalendrar,
bokningar, inventarier, utbildningar, träningsplanering etc. För att det skall vara lätt att hålla klubbens data
uppdaterad kan du delegera rättigheter för att exempelvis läsa, skriva, uppdatera och ta bort vissa uppgifter.

## 3. System Komponenter

### 3.1. Kärnkomponenter

För att systemet skall kunna utföra arbete måste användaren specificera vad det är den vill få utfört. För att
möjliggöra detta används en unik URI (Uniform Resource Identifier) och en metod för att identifiera vilken uppgift som
klienten vill ska utföras. Hur en URI och en metod skall kopplas till att utföra en specifik uppgift bestäms av en 
datadirigent. Olika uppgifter kräver olika typer av data för att kunna utföras och inhämtandet av redan lagrad data och 
information om hur ny data skall tolkas sköts av en datalagringskomponent.

### 3.2. Mellanlagringskomponenter

### 3.3. Supporting Components

## 4. Klass Diagram

## 5. Kommunikationsflöde

### 5.1. Klient-Server
