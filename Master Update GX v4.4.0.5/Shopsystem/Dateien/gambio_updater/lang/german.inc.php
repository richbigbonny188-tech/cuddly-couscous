<?php
/* --------------------------------------------------------------
   german.inc.php 2020-08-24
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

define('BUTTON_AUTO_UPDATER', 'Zum Gambio Store');
define('BUTTON_CONTINUE', 'Fortfahren');
define('BUTTON_INSTALL', 'Updates durchf&uuml;hren');
define('BUTTON_GAMBIO_PORTAL', 'Zum Gambio Kundenportal');
define('BUTTON_LOGIN', 'Anmelden');
define('BUTTON_CONFIGURE', 'Zur Updates-Konfiguration');
define('BUTTON_SHOW_UPDATES', 'Zur Updates-&Uuml;bersicht');
define('BUTTON_SHOP', 'Zum Shop');
define('BUTTON_CHECK_PERMISSIONS', 'Rechte erneut &uuml;berpr&uuml;fen');
define('BUTTON_CONNECT', 'Verbinden');
define('BUTTON_CONNECT_NEW', 'Neu verbinden');
define('BUTTON_SET_PERMISSIONS', 'Rechte setzen');
define('BUTTON_CHECK_DELETE_FILES', 'Erneut pr&uuml;fen');
define('BUTTON_CHECK_MOVE', 'Erneut pr&uuml;fen');
define('BUTTON_CREATE_BACKUP', 'Dateien downloaden');
define('BUTTON_DELETE_FILES', 'Veraltete Dateien l&ouml;schen');
define('BUTTON_MOVE', 'Durchf&uuml;hren');
define('BUTTON_SKIP', 'Installationsfortsetzung erzwingen');
define('BUTTON_DOWNLOAD_FILELIST_TO_DELETE', 'Download der Löschliste');
define('BUTTON_GAMBIO_STORE', 'Zum Gambio Store');
define('BUTTON_GAMBIO_CACHE', 'Zur Cache-Seite');

define('HEADING_INSTALLATION_SERVICE', 'Gambio Installations-Service');
define('HEADING_INSTALLATION', 'Zur Installation');
define('HEADING_LOGIN', 'Login');
define('HEADING_UPDATES', 'Updates');
define('HEADING_FTP_DATA', 'FTP-Daten');
define('HEADING_REMOTE_CONSOLE', 'Remote-Konsole');
define('HEADING_WHICH_VERSION', 'Shopversion ausw&auml;hlen');
define('HEADING_INSTALLATION_SUCCESS', 'Installation abgeschlossen');
define('HEADING_PROGRESS', 'Installationsfortschritt');
define('HEADING_WRONG_PERMISSIONS', 'Folgende Dateien oder Ordner haben keine vollen Schreibrechte (777):');
define('HEADING_NEED_TO_DELETE', 'Folgende Dateien oder Ordner m&uuml;ssen gel&ouml;scht werden:');
define('HEADING_MOVE', 'Folgende Dateien oder Ordner m&uuml;ssen verschoben werden:');
define('HEADING_RENAME', 'Folgende Dateien oder Ordner m&uuml;ssen umbenannt werden:');
define('HEADING_INSTALLATION_CLEAR_CACHE', 'Shop-Caches');
define('HEADING_INSTALLATION_CHECK_FOR_UPDATES', 'Verfügbare Updates');
define('HEADING_DELETED_FILES', 'Gelöschte Dateien und Verzeichnisse');
define('HEADING_MOVED', 'Folgende Dateien oder Ordner wurden verschoben:');
define('HEADING_RENAMED', 'Folgende Dateien oder Ordner wurden umbenannt:');
define('HEADING_PERMISSIONS_SET', 'Folgende Dateien oder Ordner haben nun volle Schreibrechte (777):');
define('HEADING_VERSION_INFO', 'Versions-Info (Changelog)');

define('LABEL_PROTOCOL', 'Protokoll:');
define('LABEL_FTP', 'FTP');
define('LABEL_SFTP', 'SFTP');
define('LABEL_VERSION', 'Version:');
define('LABEL_EMAIL', 'E-Mail:');
define('LABEL_PASSWORD', 'Passwort:');
define('LABEL_FTP_SERVER', 'FTP-Server');
define('LABEL_FTP_USER', 'FTP-Benutzer');
define('LABEL_FTP_PASSWORD', 'FTP-Passwort');
define('LABEL_FTP_PORT', 'FTP-Port');
define('LABEL_FTP_PASV', 'passiv:');
define('LABEL_DIR_UP', 'Verzeichnis nach oben');
define('LABEL_FORCE_VERSION_SELECTION', 'Versionsauswahl erzwingen');
define('LABEL_VERSION_INFO_CONFIRMATION', 'Versions-Info zur Kenntnis genommen');
define('DESCRIPTION_FORCE_VERSION_SELECTION', 'Wenn ein Update-Vorgang nach der Installation abgebrochen wurde oder fehlgeschlagen ist, kann mit der Option "Versionsauswahl erzwingen" ein erneuter Update-Vorgang gestartet werden. Bitte wähle dann die Shopversion, die der Shop VOR dem Update hatte.');

define('TEXT_INSTALLATION_SERVICE', 'Du m&ouml;chtest die Installation nicht selbst durchf&uuml;hren? Nutze unseren Installations-Service!');
define('TEXT_INSTALLATION', 'W&auml;hle die gew&uuml;nschte Sprache f&uuml;r deine Installation.');
define('TEXT_LOGIN', 'Melde dich bitte mit der E-Mail-Adresse und dem Passwort deines Adminstrator-Shop-Kontos an.');
define('TEXT_LOGIN_ERROR', 'Die E-Mail-Adresse oder das Passwort ist fehlerhaft.');
define('TEXT_UPDATES', 'Folgende Updates wurden gefunden:');
define('TEXT_WHICH_VERSION', 'Welche Shopversion hast du aktuell?<br />');
define('TEXT_LANGUAGE', 'Sprache');
define('TEXT_SECTION_NAME', 'section-Name');
define('TEXT_PHRASE_NAME', 'Phrasen-Name');
define('TEXT_CHANGELOG_HINT', 'Eine Übersicht aller Änderungen und Korrekturen findest du ');
define('TEXT_CHANGELOG_LINK', 'hier.');
define('TEXT_ERRORS', 'Es ist ein Fehler aufgetreten. Bitte spiele deine Sicherung wieder ein.');
define('TEXT_ERROR_TIMEOUT', 'Maximale Ausf&uuml;hrungszeit des Servers erreicht: Update konnte nicht vollst&auml;ndig ausgef&uuml;hrt werden.');
define('TEXT_ERROR_PARSERERROR', 'Falscher R&uuml;ckgabewert:<br />');
define('TEXT_ERROR_NO_RESPONSE', 'Unbekannter R&uuml;ckgabewert: Update wurde aus unbekannten Gr&uuml;nden abgebrochen.');
define('TEXT_ERROR_500', 'Interner Server Error: Update wurde aus unbekannten Gr&uuml;nden abgebrochen.');
define('TEXT_ERROR_UNKNOWN', 'Unbekannter Fehler.');
define('TEXT_SQL_ERRORS', 'Das Update wurde nicht vollst&auml;ndig ausgef&uuml;hrt. Bitte spiele deine Datenbanksicherung wieder ein. Folgende SQL-Fehler sind aufgetreten:');
define('TEXT_SECTION_CONFLICT_REPORT', 'Folgende Phrasen werden durch individuell angelegte section-Sprachdateien &uuml;berladen, so dass momentan die von dir soeben gew&auml;hlten neuen Phrasen-Texte nicht im Shop angezeigt werden. Wenden dich daher mit dieser Information an deinen Programmierer.<br />');
define('TEXT_DELETE_LIST', 'Bitte l&ouml;sche nun folgende Dateien bzw. Verzeichnisse von deinem Server:');
define('TEXT_INSTALLATION_AUTO_UPDATE_AVAILABLE',
    '<br /><span class="alert alert-warning" style="display:inline-block">Es stehen weitere Updates über den Gambio Store zur Verfügung. Rufe den Gambio Store für weitere Informationen auf.</span>');
define('TEXT_INSTALLATION_END_OF_EYECANDY_SUPPORT_WARNING',
    '<br /><span class="alert alert-warning" style="display:inline-block">Bitte beachte, dass die älteren Templates EyeCandy und MobileCandy ab dieser Shopversion nicht mehr unterstützt werden. Sofern du bisher EyeCandy verwendet hast, wurde automatisch das aktuelle Template Honeygrid aktiviert, um die Shopfunktion nach dem Update zu gewährleisten. Sofern du bisher das Zusatztemplate MobileCandy verwendet hast, wurde dieses deaktiviert. Beide veralteten Templates können in den Shopeinstellungen wieder aktiviert werden, Gambio gewährleistet allerdings die korrekte Funktion mit Shopversionen ab GX3.7 nicht mehr.</span>');
define('TEXT_INSTALLATION_SUCCESS_WARNING', '<br /><span class="alert alert-warning" style="display:inline-block">Wenn du das Modul StyleEdit verwendest, muss dieses im Rahmen des Service Packs aktualisiert werden.<br /><br /><u><b>Findet die Aktualisierung des StyleEdits nicht statt, treten Fehler im Shop auf</u></b>. Bitte beachte hierzu den Abschnitt &quot;Installation Bearbeitungsmodus StyleEdit&quot; der Installationsanleitung.<br /><br /><br /><u><b>Nach Abschluss des Updates müssen die Originalvorlagen für die E-Mail-Vorlagen &quot;Bestellbestätigung&quot; und &quot;Admin: Änderung Bestellstatus&quot; wiederhergestellt werden, da es sonst zu Fehlern im Shop kommen kann.</u></b><br /><br />Gehe hierzu im Gambio Admin zu <b>Kunden &gt; E-Mails &gt; E-Mail Vorlagen</b> und wähle für die genannten Vorlagen &quot;Original wiederherstellen&quot; aus und klicke auf OK. Führen dies bitte sowohl für die HTML- als auch die Textvorlagen in beiden Sprachen (Deutsch und Englisch) durch. Leere abschließend unter <b>Toolbox &gt; Cache leeren</b> den Cache für die E-Mail-Vorlagen. Bitte beachte, dass individuelle Änderungen hierdurch verloren gehen und ggf. erneut vorgenommen werden müssen.<br /><br /><br />Leere abschließend den Cache deines Browsers, um Darstellungsfehler zu vermeiden.</span>');
define('TEXT_INSTALLATION_SUCCESS', 'Das Update wurde abgeschlossen.<br /><br />Überprüfe nun im Gambio Store, ob weitere Updates für in deinem Shop installierte Module oder Themes vorliegen. Bitte installiere diese dann unbedingt, um einen störungsfreien Betrieb sicherzustellen.');
define('TEXT_INSTALLATION_HTACCESS_WARNING', '<br /><span class="alert alert-warning" style="display:inline-block">Mit Abschluss dieses Updates muss die .htaccess-Datei im Hauptverzeichnis des Shops auf dem FTP-Server aktualisiert beziehungsweise neu hinzugefügt werden, sofern noch keine .htaccess Datei vorher verwendet wurde.<br /><br />Wenn du keine eigenen Anpassungen in der Datei vorgenommen hast oder die Datei noch nicht im Hauptverzeichnis des Shops auf dem FTP-Server vorhanden ist, kopiere einfach die Vorlage aus dem Verzeichnis gm/seo_boost_an/.htaccess in das Hauptverzeichnis deines Shops, andernfalls gleiche deine Anpassungen mit der dortigen Vorlage ab.<br /><br />Das Hauptverzeichnis des Shops auf dem FTP-Server ist das Verzeichnis, in dem du die Verzeichnisse /cache, /admin, /gm etc. findest.</span>');
define('TEXT_INSTALLATION_INDEX_SUPRESSING_WARNING', '<br /><span class="alert alert-warning" style="display:inline-block">Mit diesem Update kannst du in der URL der Startseite die Endung &quot;index.php&quot; wahlweise ein- oder ausblenden. Die Einstellung dafür findest du unter &quot;SEO-Boost&quot; im Gambio Admin.<br />Beachte bitte außerdem die veränderte Wirkung der SEO Boost-Option für Sprachcodes in URLs, die sich jetzt auf mehr Seiten auswirkt.</span>');
define('TEXT_INSTALLATION_SUCCESS_CACHE_REBUILD_ERROR', 'Alle Updates wurden erfolgreich installiert.<span class="alert alert-danger" >Achtung: Die Caches konnten nicht geleert werden.<br />Bitte leere die Caches im Gambio Admin!</span><br />Überprüfe anschließend im Gambio Store, ob weitere Updates für in deinem Shop installierte Module oder Themes vorliegen. Bitte installiere diese dann unbedingt, um einen störungsfreien Betrieb sicherzustellen.');
define('TEXT_PROGRESS', 'Bitte habe ein wenig Geduld.');
define('TEXT_CURRENT', 'Folgendes Update wird gerade installiert: ');
define('TEXT_SET_PERMISSIONS', 'Du kannst die Rechte entweder selbst mit einem FTP-Programm oder &uuml;ber die FTP-Funktion des Updaters setzen. F&uuml;r Letzteres gib bitte im folgenden Formular deine FTP-Daten ein und klicken auf &quot;Verbinden&quot;.<br />
Anschlie&szlig;end navigiere zum Verzeichnis, in dem sich der Shop befindet und starten die Rechtevergabe, indem du auf den Button &quot;Rechte setzen&quot; klickst. ');
define('TEXT_DELETE_FILES', 'Du kannst die Dateien und Ordner entweder selbst mit einem FTP-Programm oder &uuml;ber die FTP-Funktion des Updaters l&ouml;schen. F&uuml;r Letzteres gib bitte im folgenden Formular deine FTP-Daten ein und klicken auf &quot;Verbinden&quot;.<br />
Anschlie&szlig;end navigiere zum Verzeichnis, in dem sich der Shop befindet und starte den L&ouml;schvorgang, indem du auf den Button &quot;Veraltete Dateien l&ouml;schen&quot; klickst. ');
define('TEXT_MOVE', 'Du kannst die &Auml;nderungen mit einem FTP-Programm oder &uuml;ber die FTP-Funktion des Updaters durchf&uuml;hren. F&uuml;r Letzteres gib bitte im folgenden Formular deine FTP-Daten ein und klicke auf &quot;Verbinden&quot;.<br />
Anschlie&szlig;end navigiere zum Verzeichnis, in dem sich der Shop befindet und starte den Vorgang, indem du auf den Button &quot;Durchf&uuml;hren&quot; klickst. ');
define('TEXT_NO_CONFIGURATION', 'Die zu installierenden Updates haben keine Konfiguration. Fahre mit Klick auf &quot;' . BUTTON_INSTALL . '&quot; fort.');
define('TEXT_NO_UPDATES', 'Es wurden keine installierbaren Updates für deine aktuelle Shopversion gefunden.');
define('TEXT_PERMISSIONS_OK', 'Die Dateirechte sind korrekt gesetzt.');
define('TEXT_DELETE_FILES_OK', 'Veraltete Dateien und Ordner wurden gel&ouml;scht.');
define('TEXT_MOVE_OK', 'Das Umbenennen bzw. Verschieben wurde erfolgreich durchgef&uuml;hrt.');
define('TEXT_CURRENT_DIR', 'aktuelles Verzeichnis: ');
define('TEXT_TEMPLATE_NOTIFICATION', 'Dein aktuelles Template scheint ohne Anpassungen nicht kompatibel mit der neuen Architektur zu sein. Daher wird vorerst das EyeCandy-Template aktiviert.');
define('TEXT_SKIP', 'Du kannst die Installation fortsetzen, wenn du sicher bist, dass die Rechte bereits korrekt gesetzt sind und deren Erkennung aus technischen Gründen fehlschlägt.');
define('TEXT_INSTALLATION_CLEAR_CACHE', 'Bitte habe ein wenig Geduld, bis die Caches des Shops neu aufgebaut wurden...');
define('TEXT_INSTALLATION_CHECK_FOR_UPDATES', 'Es wird überprüft, ob weitere Updates zur Verfügung stehen...');
define('TEXT_DELETED_FILES', 'Die gelisteten Dateien bzw. Verzeichnisse wurden gelöscht. Du kannst eine Sicherung über den Button "Dateien downloaden" herunterladen.<br /><br />Klicke auf den Button "Fortfahren", um die Installation fortzusetzen.');
define('TEXT_PERMISSIONS_SET', 'Die gelisteten Dateien bzw. Verzeichnisse haben nun die korrekten Schreibrechte.<br /><br />Klicke auf den Button "Fortfahren", um die Installation fortzusetzen.');
define('TEXT_NOT_ALL_FILES_UPLOADED', 'Das Service Pack wurde nicht vollständig hochgeladen. Bitte lade das gesamte Service Pack noch einmal auf deinen Webserver.');
define('TEXT_NOT_ALL_SE_V2_FILES_UPLOADED', 'Das StyleEdit Modul wurde nicht vollständig hochgeladen. Bitte lade das gesamte StyleEdit Verzeichnis noch einmal auf deinen Webserver.');
define('TEXT_NOT_ALL_SE_V3_FILES_UPLOADED', 'Das StyleEdit3 Modul wurde nicht vollständig hochgeladen. Bitte lade das gesamte StyleEdit3 Verzeichnis noch einmal auf deinen Webserver.');
define('TEXT_SHOW_FILES', 'Dateien anzeigen');

define('TEXTCONFLICTS_LABEL', 'Textkonflikte');
define('TEXTCONFLICTS_TEXT', 'Welche Textversion m&ouml;chtest du &uuml;bernehmen?');
define('TEXTCONFLICTS_OLD', 'alt');
define('TEXTCONFLICTS_NEW', 'neu');

define('ERROR_FTP_CONNECTION', 'Es konnte keine FTP-Verbindung zu \'%s\' hergestellt werden. &Uuml;berpr&uuml;fe die FTP-Adresse!');
define('ERROR_FTP_DATA', 'Der FTP-Benutzer \'%s\' oder das FTP-Passwort ist falsch!');
define('ERROR_FTP_NOT_INSTALLED', 'Der Server unterst&uuml;tzt leider kein FTP. Bitte wende dich an deinen Provider mit der Bitte die FTP-Funktionen f&uuml;r PHP freizuschalten.<br />Alternativ kannst du jetzt die Anpassungen mit einem FTP-Programm durchf&uuml;hren. Klicke anschlie&szlig;end auf &quot;Erneut pr&uuml;fen&quot;, um den Update-Prozess fortzuf&uuml;hren.');
define('ERROR_FTP_NO_LISTING', 'Der Server kann leider keine Verzeichnisse per FTP auslesen. Ursache k&ouml;nnte z. B. eine serverseitige Firewall sein. Bitte wende dich an deinen Provider mit der Bitte die PHP-Funktion &quot;ftp_nlist&quot; zu &uuml;berpr&uuml;fen.<br />Alternativ kannst du jetzt die Anpassungen mit einem FTP-Programm durchf&uuml;hren. Klicke anschlie&szlig;end auf &quot;Erneut pr&uuml;fen&quot;, um den Update-Prozess fortzuf&uuml;hren.');
define('ERROR_SFTP_CONNECTION', 'Es konnte keine SFTP-Verbindung hergestellt werden. &Uuml;berpr&uuml;fe bitte die Zugangsdaten!');

define('ERROR_SQL_UNKNOWN', 'Ein unbekannter Fehler ist während des Updates "x.x.x.x" aufgetreten.');

define('LABEL_FORCE_VERSION_SELECTION_BTN', 'Versionsauswahl erzwingen');

define('REQUIREMENT_WARNING', '<p>Für den Gambio-Shop wird mindestens <strong>PHP ###minPHPVersion### </strong> und <strong>MySQL ###minMySQLVersion###</strong> benötigt.</p><p>Deine PHP-Version: <strong>###yourPHPVersion###</strong><br />Deine MySQL-Version: <strong>###yourMySQLVersion###</strong></p><p>Bitte kontaktiere deinen Provider, um die entsprechenden Versionen anzupassen.</p>');

define('TEXT_PAYPAL_NOTIFICATION', '<span class="alert alert-warning" style="display:inline-block"><strong>ACHTUNG: Du nutzt ein veraltetes PayPal-Modul!</strong><br /><p>Wir empfehlen dir, ab sofort das aktuelle PayPal-Modul einzusetzen. Du kannst es jetzt im Admin unter Module -> Zahlungsweisen aktivieren.</p><p>Weitere Informationen: <a href="https://www.gambio.de/7pM9Y" target="_blank">https://www.gambio.de/7pM9Y</a></p></span>');
define('HEADING_ERROR_REPORTS', 'Fehlerberichte');
define('CHECKBOX_ERROR_REPORTS', 'Ich möchte zur Verbesserung der Software beitragen und stimme zu, dass beim Auftreten eines Fehlers automatisch ein Fehlerbericht an Gambio gesendet wird.');
define('TEXT_ERROR_REPORTS', '
<p>
    Fehlerberichte enthalten
    <ul>
        <li>Server-Informationen (z.B. PHP- und mySQL-Versionen, Einstellungen, geladene Module)</li>
        <li>Laufzeit-Informationen (z.B. Script-Name/URL, Sprache, IP, Zeitstempel, Browser, genutzte Parameter)</li>
        <li>Fehlerdetails (z.B. Fehlermeldung, betroffener Code-Abschnitt)</li>
    </ul>
</p>
<div>
    Fehlerberichte können unter Umständen auch persönliche Daten enthalten, z.B. wenn es zu Fehlern während der Verarbeitung von Bestelldaten kommt. Bitte prüfe, ob eine Informierung deiner Kunden, zum Beispiel im Rahmen der Datenschutzerklärung, nötig ist. Das Senden der Fehlerberichte kannst du im Modul-Center jederzeit wieder deaktivieren.
</div>');


# Admin Feed
define('HEADING_ADMIN_FEED_SHOP_INFORMATION', 'Datenverarbeitung von Shop-Informationen durch Gambio zustimmen');
define('TEXT_ADMIN_FEED_SHOP_INFORMATION_1',  'Indem du der Datenverarbeitung von Shop-Informationen zustimmst, ermöglichst du uns, dir möglichst passende und informative Inhalte (wie z.B. Admin-News oder Admin-Infobox-Benachrichtigungen) in deinem Shop anzuzeigen. Die Zustimmung erlaubt dir zusätzlich die Nutzung zukünfitger Features, welche ebenfalls eine Verarbeitung der Shop-Informationen voraussetzen. Die Shop-Informationen enthalten dabei keine personen- oder handelsbezogenen Daten!');
define('TEXT_ADMIN_FEED_SHOP_INFORMATION_2',  'Die Shop-Informationen enthalten dabei keine personenbezogenen Daten, sondern lediglich Informationen wie zum Beispiel die aktuelle Shop-Version, URL, Shop Key, aktive Sprachen, aktive Länder, verwendetes Server-System und dessen Konfigurationen für PHP und MySQL, verfügbare und installierte Shop- und Hub-Module, verfügbare und verwendetes Templatesystem sowie dessen Version, vorhandene Usermods, vorhandene GXModules, auffällige oder gefährliche Dateien im Dateisystem des Shops, vorhandene Update-Quittungen, Indikator für einen globalen Usermod-Ordner, Indikator für das UPM, installierte Updates und über den AutoUpdater heruntergeladene Updates.');
define('CHECKBOX_ADMIN_FEED_SHOP_INFORMATION', 'Ich stimme der Verarbeitung der Shop-Informationen durch Gambio zu');

define('HEADING_SUNNYCASH', 'Danke-Gutscheine - SunnyCash Schnittstelle (früher Ovisto)');
define('TEXT_SUNNYCASH', 'Über das Danke-Gutschein-Modul können sich Kunden nach Abschluss der Bestellung einen Dankeschön-Gutschein aus einer vordefinierten Auswahl aussuchen. Die Gutscheine werden über die Schnittstelle zu unserem Partner SunnyCash (SunnyCash.de) geladen und direkt auf der Checkout-Success-Seite angezeigt. Dabei werden keinerlei Kunden- oder Bestelldaten übertragen. Einkäufe werden somit positiv verstärkt und die Kundenbindung wird gesteigert. Das Modul ist standardmäßig aktiviert und kann über den Gambio Admin konfiguriert und selbstverständlich auch deaktiviert werden. In vorherigen Versionen wurde der Service über die Solute GmbH angeboten, welche den Dienst jedoch zukünftig nicht mehr anbieten wird.');