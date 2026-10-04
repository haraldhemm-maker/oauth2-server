# PHP_CodeSniffer in Eclipse installieren

## Schritt 1: PHP_CodeSniffer auf dem System installieren

Bevor Eclipse das Tool nutzen kann, muss PHP_CodeSniffer installiert sein. Am 
einfachsten gelingt dies global via Composer:

> composer global require "squizlabs/php_codesniffer=*"

## Schritt 2: PHPCS als "External Tool" in Eclipse einrichten

Dieser Weg funktioniert unabhängig von Eclipse-Versionen und Drittanbieter-
Plugins, die oft veraltet sind.
1. Öffne Eclipse und gehe im oberen Menü auf Run -> External Tools -> External 
    Tools Configurations...
2. Mache einen Doppelklick auf Program in der linken Spalte, um eine neue 
    Konfiguration zu erstellen.
3. Benenne die Konfiguration (z. B. PHP_CodeSniffer).
4. Konfiguriere den Reiter Main:
	• Location: Klicke auf Browse File System... und wähle den Pfad zu deiner 
        phpcs-Ausführungsdatei (z. B. /usr/local/bin/phpcs unter Linux/macOS oder 
    C:\Users\DeinName\AppData\Roaming\Composer\vendor\bin\phpcs.bat 
        unter Windows).
	• Working Directory: Trage ${project_loc} ein (nutze den Button 
        Variables...).
	• Arguments: Trage ${resource_loc} ein. Wenn du einen festen Standard 
        erzwingen willst, nutze --standard=PSR12 ${resource_loc}.
5. Konfiguriere den Reiter Common:
    Setze unter Display in favorite menu ein Häkchen bei External Tools, 
    damit das Tool schnell über die Werkzeugleiste erreichbar ist.
6. Klicke auf Apply und Close.

Nutzung: Wähle nun eine PHP-Datei oder einen Ordner im Project Explorer aus 
und klicke im Eclipse-Menü auf den kleinen Pfeil neben dem grünen "Run"-Symbol 
mit der Werkzeugtasche (External Tools) und wähle PHP_CodeSniffer. Die Fehler 
werden in der Console View ausgegeben.

