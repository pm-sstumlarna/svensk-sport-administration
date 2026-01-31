# Kravspecifikation för Svensk Sport Administration

## Inledning

### Syfte

Detta är en kravspecifikation för ett system som syftar till att hantera och administrera sportklubbars medlemsregister,
fakturering och utbildningsplanering. Systemet är utformat för att stödja både interna och externa användare, inklusive
mjukvaruutvecklare, systemadministratörer och klubbadministratörer.

### Omfattning

### Definierande termer

### Referenser

## Övergripande systembeskrivning

### Produktbeskrivning

Detta API är till för att hantera administrationen i svenska sportklubbar. Detta inkluderar att underlätta
styrelsearbete, hantering av medlemsregister, fakturering, utbildningsplanering och kommunikation med medlemmar.

### Användargrupper och intressenter

#### Mjukvaruutvecklare

Detta är den grupp individer som ser till att detta projektet utvecklas och därför en väldigt viktig del i projektets
framgång. Aspekter som en mjukvaruutvecklare kan ha är:

- att källkoden skall vara lätt att förstå
- att processen för att sätta upp en test- och utvecklingsmiljö är smidig

#### Systemadministratör

Den som vill använda sig av mjukvaran som utvecklas i projektet vänder sig oftast till en systemadministratör som har
behörighet att installera och konfigurera mjukvaran. Viktiga aspekter för en systemadministratör kan vara:

- att mjukvaran skall fungera i olika exekveringsmiljöer
- att installation och konfiguration skall vara enkelt
- att systemresurser skall nyttjas så effektivt som möjligt
- att det finns god dokumentation om hur man integrerar externa system

#### Klubbadministratör

Klubbadministratören är den person som har det övergripande ansvaret för föreningens administration i systemet.
Detta inkluderar hantering av medlemsregister, fakturering och kommunikation på föreningsnivå. Viktiga aspekter för
en klubbadministratör är:

- att enkelt kunna importera och exportera medlemsdata
- att kunna hantera medlemsavgifter och fakturering
- att ha en överblick över föreningens samtliga aktiviteter och lokaler
- att kunna kommunicera med alla medlemmar eller specifika grupper

#### Idrottsledare

Idrottsledarens ansvar är att planera och sköta träningarna och att tillsammans med idrottsutövaren formulera och följa
upp målen med träningen. I systemet förväntas ledaren kunna:

- planera och schemalägga träningspass och matcher
- närvaroregistrera deltagare för att ligga till grund för bidragsansökningar
- kommunicera direkt med idrottsutövare och deras vårdnadshavare
- dokumentera träningsplaner och utveckling

#### Idrottsutövare

En idrottsutövare kanske inte är den som interagerar mest med systemet men är ju den person som hela systemet är
uppbyggt för att administrera. Funktionalitet som efterfrågas av idrottsutövare kan vara:

- importera en kalender med idrottsaktiviteter
- att komma i kontakt med tränare och andra idrottsledare
- att kunna följa sin idrottsliga utveckling och sätta upp träningsmål
- att kunna visa och hantera sina egna resultat

#### Vårdnadshavare

I de fall en idrottsutövare ännu inte är myndig så är det vårdnadshavaren som ska informeras om allt som händer i
klubben. Vårdnadshavaren behöver:

- se barnets schema och aktiviteter
- kunna anmäla frånvaro eller svara på kallelser
- få viktiga meddelanden från ledare och klubben
- hantera barnets medlemsuppgifter och betalningar

#### Idrottsförbund

Idrottsförbunden (specialidrottsförbund) sätter ramarna för idrottens regler och tävlingssystem. De har ett intresse
av att:

- få in statistik om deltagande och medlemsantal
- integrera med förbundsspecifika tävlingssystem
- säkerställa att ledare har rätt utbildningsnivå

#### Myndigheter

Myndigheter, så som Riksidrottsförbundet (RF) och kommuner, är intressenter främst genom bidragshantering. Deras
aspekter är:

- att närvarodata är tillförlitlig och verifierbar för LOK-stöd (Lokalt aktivitetsstöd)
- att personuppgiftshantering sker i enlighet med GDPR
- att statistik kan redovisas på ett standardiserat sätt

### Systemkrav

### Begränsningar och antaganden

## Funktionella krav

### 1. Medlemshantering (Klubbadministratör, Vårdnadshavare, Myndigheter)

- **FK1.1 Importera/Exportera medlemmar**: Systemet ska stödja import och export av medlemsdata i standardiserade
  format.
- **FK1.2 Hantera personuppgifter**: Systemet ska kunna lagra och uppdatera nödvändiga personuppgifter i enlighet med
  GDPR.
- **FK1.3 Medlemsregister**: Systemet ska tillhandahålla ett centralt register över alla medlemmar.

### 2. Aktivitet och Planering (Idrottsledare, Idrottsutövare, Vårdnadshavare)

- **FK2.1 Schemaläggning**: Systemet ska tillåta skapande av träningspass, matcher och andra aktiviteter.
- **FK2.2 Kalenderintegration**: Idrottsutövare och vårdnadshavare ska kunna exportera/importera aktiviteter till
  externa kalenderapplikationer.
- **FK2.3 Närvaroregistrering**: Idrottsledare ska kunna registrera närvaro för deltagare vid varje aktivitet.
- **FK2.4 Kallelser och anmälan**: Systemet ska kunna skicka ut kallelser till aktiviteter som användare kan svara på.

### 3. Ekonomi (Klubbadministratör, Vårdnadshavare)

- **FK3.1 Fakturering**: Systemet ska kunna generera och skicka ut fakturor för medlems- och träningsavgifter.
- **FK3.2 Betalningsuppföljning**: Klubbadministratörer ska kunna se status på fakturor och markera dem som betalda.
- **FK3.3 Hantera medlemsavgifter**: Systemet ska stödja definition av olika avgiftsnivåer för olika medlemskategorier.

### 4. Kommunikation (Samtliga intressenter)

- **FK4.1 Meddelandeutskick**: Systemet ska kunna skicka meddelanden (e-post/sms/notiser) till individer eller 
  definierade grupper.
- **FK4.2 Kontaktinformation**: Användare ska enkelt kunna hitta kontaktuppgifter till ledare och administratörer inom
  sin grupp/förening.

### 5. Statistik och Rapportering (Idrottsförbund, Myndigheter, Klubbadministratör)

- **FK5.1 LOK-stödsrapporter**: Systemet ska generera underlag för LOK-stödsansökningar baserat på verifierad
  närvarodata.
- **FK5.2 Medlemsstatistik**: Systemet ska kunna sammanställa statistik över medlemsantal, åldersfördelning och
  deltagande.
- **FK5.3 Utbildningsnivåer**: Systemet ska kunna registrera och följa upp ledarnas utbildningsnivåer.

### 6. Uppföljning och Utveckling (Idrottsledare, Idrottsutövare)

- **FK6.1 Dokumentation av träningsmål**: Idrottsledare och utövare ska kunna sätta upp och följa upp individuella och
  gruppbaserade mål.
- **FK6.2 Resultathantering**: Systemet ska tillåta registrering och visning av tävlings- och träningsresultat.

### 7. Säkerhetskrav

- **FK7.1 Behörighetskontroll för känsliga uppgifter**: För att hämta känsliga personuppgifter, såsom personnummer,
  måste autentiseringen innehålla en ACR-nivå (Authentication Context Class Reference) som intygar att någon form utav
  U2F-nyckel (eller motsvarande stark autentisering) har använts.

## Användningsfall

I detta avsnitt beskrivs systemets funktionalitet genom användningsfall (Use Cases) för att ge en tydligare bild av hur
olika intressenter interagerar med systemet.

### UC1. Registrera organisation

### UC2. Importera medlemsregister

### UC3. Registrera närvaro

## Kvalitetsattribut

## Externa gränssnitt

### Säkerhet


## Bilagor

## Referenser

