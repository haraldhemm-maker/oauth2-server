#!/bin/bash

# Pfad zur Kontrolldatei definieren
ZEITSTEMPEL_DATEI="$HOME/public_html/app/oauth2-server/tmp/.cleanup_last_call"

# Pruefen, ob die Datei heute bereits geaendert wurde
if [ -f "$ZEITSTEMPEL_DATEI" ] && [ "$(date +%Y-%m-%d)" = "$(date -r "$ZEITSTEMPEL_DATEI" +%Y-%m-%d)" ]; then
    # Der Job lief heute schon, also brechen wir ab
    exit 0
fi

# --------------------------------------------------
# HIER DEINEN EIGENTLICHEN BEFEHL EINFÜGEN
# Beispiel: echo "Job ausgeführt am $(date)" >> $HOME/log.txt
# --------------------------------------------------
cd $HOME/public_html/app/oauth2-server
/usr/bin/php php/cleanup.php >> tmp/cleanup.log 2>&1

# Nach erfolgreicher Ausführung den Zeitstempel auf JETZT setzen
touch "$ZEITSTEMPEL_DATEI"

