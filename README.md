# IPSymconPulsecounter

[![IPS-Version](https://img.shields.io/badge/Symcon_Version-6.0+-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Code](https://img.shields.io/badge/Code-PHP-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)

## Dokumentation

**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Funktionsreferenz](#4-funktionsreferenz)
5. [Konfiguration](#5-konfiguration)
6. [Anhang](#6-anhang)
7. [Versions-Historie](#7-versions-historie)

## 1. Funktionsumfang

Übernahme der Zählerdaten von dem "do it yourself" 4-fach Zählermodul _Pulsecounter_ von ([stall.biz](https://www.stall.biz/project/wiffi-count-2fach-zaehler-fuer-strom-gas-wasser-und-solar)).

Getestet mit der Pulsecounter-Version **34**.

## 2. Voraussetzungen

 - IP-Symcon ab Version 6.0
 - ein Pulsecounter-Zählermodul

## 3. Installation

### a. Laden des Moduls

Die Webconsole von IP-Symcon mit _http://\<IP-Symcon IP\>:3777/console/_ öffnen.

Anschließend oben rechts auf das Symbol für den Modulstore (IP-Symcon > 5.1) klicken

![Store](docs/de/img/store_icon.png?raw=true "open store")

Im Suchfeld nun _Pulsecounter_ eingeben, das Modul auswählen und auf _Installieren_ drücken.

#### Alternatives Installieren über Modules Instanz (IP-Symcon < 5.1)

Die Webconsole von IP-Symcon mit _http://\<IP-Symcon IP\>:3777/console/_ aufrufen.

Anschließend den Objektbaum _öffnen_.

![Objektbaum](docs/de/img/objektbaum.png?raw=true "Objektbaum")

Die Instanz _Modules_ unterhalb von Kerninstanzen im Objektbaum von IP-Symcon mit einem Doppelklick öffnen und das  _Plus_ Zeichen drücken.

![Modules](docs/de/img/Modules.png?raw=true "Modules")

![Plus](docs/de/img/plus.png?raw=true "Plus")

![ModulURL](docs/de/img/add_module.png?raw=true "Add Module")

Im Feld die folgende URL eintragen und mit _OK_ bestätigen:

```
https://github.com/demel42/IPSymconPulsecounter.git
```

Anschließend erscheint ein Eintrag für das Modul in der Liste der Instanz _Modules_.

### b. Einrichtung des Geräte-Moduls

In IP-Symcon nun unterhalb des Wurzelverzeichnisses die Funktion _Instanz hinzufügen_ (_CTRL+1_) auswählen, als Hersteller _stall.biz_ und als Gerät _Pulsecounter_ auswählen.
Es wird automatisch eine I/O-Instanz vom Type Server-Socket angelegt und das Konfigurationsformular dieser Instanz geöffnet.

Hier die Portnummer eintragen, an die der Pulsecounter Daten schicken soll und die Instanz aktiv schalten.

In dem Konfigurationsformular der Pulsecounter-Instanz kann man konfigurieren, welche Variablen übernommen werden sollen.

### c. Anpassung des Pulsecounter

Der Pulsecounter muss in zwei Punkten angepaast werden

- Einrichten der IP von IP-Symcon
```
http://<ip des Pulsecounter>/?ccu:<ip von IPS>:
```
- aktivieren der automatischen Übertragung
```
http://<ip des Pulsecounter>/?param:12:<port von IPS>:
```
damit schickt der Pulsecounter zyklisch die Daten.

Gemäß der Dokumentation sind die 4 Zähler im Pulsecounter zu konfigurieren (_Modus_ und _Impuls/Einheit_) sowie ggfs der aktuelle Wert des Zählers einzustellen.

## 4. Funktionsreferenz

## 5. Konfiguration

#### Properties

| Eigenschaft                           | Typ      | Standardwert | Beschreibung |
| :------------------------------------ | :------  | :----------- | :----------- |
| Zähler 1                              | integer  | -1           | Typ des 1. Zählers |
| Zähler 2                              | integer  | -1           | Typ des 2. Zählers |
| Zähler 3                              | integer  | -1           | Typ des 3. Zählers |
| Zähler 4                              | integer  | -1           | Typ des 4. Zählers |

| Typ          | Wert |
| :----------- | :--- |
| undefiniert  | -1 |
| Elektrizität | 0 |
| Gas          | 1 |
| Wasser       | 2 |

In Abhängigkeit von dem ṮTyp_ werden jeweils 2 Variablen angelegt mit dem entsprechenden Datentyp, jeweils ein Zähler und eine Angabe der aktuellen Leistung/Verbrauch.
Falls man die Werte archivieren möchte, ist sinnvollerweise die _Aggregation_ auf _Zähler_ einzustellen.

#### Variablenprofile

Es werden folgende Variablenprofile angelegt:
* Integer<br>
Pulsecounter.sec,
Pulsecounter.Wifi

* Float<br>
Pulsecounter.kW,
Pulsecounter.kWh,
Pulsecounter.m3,
Pulsecounter.m3_h

## 6. Anhang

GUIDs
- Modul: `{C458E2BB-1B72-FE9B-B14D-929415F92B39}`
- Instanzen:
  - Pulsecounter: `{2E598E2C-32FD-0407-3EE0-496B33854129}`

## 7. Versions-Historie

- 1.14 @ 06.02.2024 09:46
  - Verbesserung: Angleichung interner Bibliotheken anlässlich IPS 7
  - update submodule CommonStubs

- 1.13 @ 05.12.2023 15:09
  - Fix: fehlerhafte Variablenprofile korrigiert

- 1.12 @ 03.11.2023 11:06
  - Neu: Ermittlung von Speicherbedarf und Laufzeit (aktuell und für 31 Tage) und Anzeige im Panel "Information"
  - update submodule CommonStubs

- 1.11 @ 04.07.2023 14:44
  - Vorbereitung auf IPS 7 / PHP 8.2
  - update submodule CommonStubs
    - Absicherung bei Zugriff auf Objekte und Inhalte

- 1.10.1 @ 07.10.2022 13:59
  - update submodule CommonStubs
    Fix: Update-Prüfung wieder funktionsfähig

- 1.10 @ 18.08.2022 12:15
  - Möglichkeit der Eingabe des Gas-Brennwerts und basierend darauf die Berechnung der "Gasenergie"
  - Funktion zum Neu-Aufbau des Archivs der "Gasenergie" aus dem "Gaszähler"
  - update submodule CommonStubs

- 1.9 @ 05.07.2022 15:40
  - Verbesserung: IPS-Status wird nur noch gesetzt, wenn er sich ändert

- 1.8.1 @ 22.06.2022 10:33
  - Fix: Angabe der Kompatibilität auf 6.2 korrigiert

- 1.8 @ 28.05.2022 12:21
  - update submodule CommonStubs
    Fix: Ausgabe des nächsten Timer-Zeitpunkts
  - einige Funktionen (GetFormElements, GetFormActions) waren fehlerhafterweise "protected" und nicht "private"

- 1.7.2 @ 17.05.2022 15:38
  - update submodule CommonStubs
    Fix: Absicherung gegen fehlende Objekte

- 1.7.1 @ 10.05.2022 15:06
  - update submodule CommonStubs

- 1.7 @ 06.05.2022 10:11
  - IPS-Version ist nun minimal 6.0
  - Anzeige der Modul/Bibliotheks-Informationen, Referenzen und Timer
  - Implememtierung einer Update-Logik
  - Überlagerung von Translate und Aufteilung von locale.json in 3 translation.json (Modul, libs und CommonStubs)
  - diverse interne Änderungen

- 1.6.1 @ 13.01.2022 15:51
  - Änderung in neuer Pulscounter-Firmware abgefangen
  - Anzeige der Modul/Bibliotheks-Informationen

- 1.5 @ 18.12.2020 14:57
  - PHP_CS_FIXER_IGNORE_ENV=1 in github/workflows/style.yml eingefügt

- 1.4 @ 12.09.2020 11:40
  - LICENSE.md hinzugefügt
  - lokale Funktionen aus common.php in locale.php verlagert
  - Traits des Moduls haben nun Postfix "Lib"
  - define's durch statische Klassen-Variablen ersetzt

- 1.3 @ 30.12.2019 10:56
  - Fix in ReceiveData()

- 1.2 @ 19.12.2019 14:09
  - Anpassungen an IPS 5.3
    - Formular-Elemente: 'label' in 'caption' geändert

- 1.1 @ 15.12.2019 11:53
  - ReceiveData() umgebaut
  - mehr debug

- 1.0 @ 10.12.2019 09:53
  - Initiale Version
