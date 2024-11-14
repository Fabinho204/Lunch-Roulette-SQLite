# Siemens Lunch Roulette

## DOWNLOAD THE ZIP FILE ATTACHED

### Guide

### FPDF downloaden

http://www.fpdf.org/en/download.php

Downloade die neueste Version von FPDF und erstelle manuell einen "fpdf" Ordner, indem das gedownloadete File entpackt wird (Selbe Ebene wie andere Folder zB css, js, navbar etc.)

Wichtig noch: in der php.ini file muss bei ";extension=sqlite3" das Semicolon ";" entfernt werden. Das ; dient als auskommentieren.

#### initialize_sqlite.php ausführen

Diese PHP File erstellt alle benötigten Tabellen.
Einfach in der URL /initialize_sqlite.php anfügen (einmal).

#### Admin

Über den Login Button kann ein Registrierformular aufgerufen werden, solange noch kein Admin registriert wurde. Falls doch kann sich dieser registrierte Admin hier nurnoch anmelden.

#### Roulette ausführen

Der angemeldete Admin gelangt nun auf sein Dashboard, wo er verschiedene CRUD Optionen hat und das Roulette ausführen kann.
