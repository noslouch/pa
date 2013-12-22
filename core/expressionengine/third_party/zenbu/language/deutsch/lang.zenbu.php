<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//
//
// Zenbu language file
// -------------------
// The Zenbu addon uses strings from the core ExpressionEngine language files,
// in addition to the languages strings below
//
//

$lang = array(

'zenbu_module_name'		=> 'Zenbu',

'zenbu_module_description'	=> 'Optimierte Übersicht der Eintragsliste im Control Panel',

//
// Control Panel
//
//
'settings' => 'Einstellungen',

'entries' => 'Einträge',

'loading' => 'Lade...',

//
// VIEWS
//
// - Search options


'any_custom_fields_titles' => 'Irgend ein Titel oder eigenes Feld',

'by_channel' => 'Alle Channels',

'by_category' => 'Alle Kategorien',

'by_author' => 'Alle Autoren',

'by_status' => 'Nach Status',

'all_statuses' => 'Alle Statuse',

'is_sticky' => 'Fixiert',

'not_sticky' => 'Nicht fixiert',

'sticky_both' => 'Fixiert und Nicht fixiert',

'by_entry_date' => '...',

'by_limit' => 'Anzahl Einträge',

'by_categories' => 'Alle Kategorien',

'entries_with_no_categories' => 'Keine Kategorie',

'by_search_in' => 'Suche in...',

'titles_and_fields' => 'Titel und normaler Feldinhalt',

'titles_only' => 'Titel',

'entry_title' => 'Eintragstitel',

'entry_id' => 'Eintrag ID',

'id' => '#',

'focused_field_search' => 'Fokussierte Feld-Suche',

'keyword' => "Stichwort  ",

'custom_fields' => 'Eigene Felder',

'autosave'						=> 'Autosave',

'orderby' => "Geordnet nach",

'asc' => "Aufsteigende Reihenfolge",

'desc' => "Absteigende Reihenfolge",

'in' => 'enthält',

'not_in' => 'enthält nicht',

'is' => 'ist',

'isnot' => 'ist nicht',

'contains' => 'enthält',

'doesnotcontain' => 'enthält nicht',

'beginswith' =>	'beginnt mit',

'doesnotbeginwith' => 'beginnt nicht mit',

'endswith' => 'endet mit',

'doesnotendwith' => 'endet nicht mit',

'containsexactly' => 'enthält genau',

'isempty' => 'ist leer',

'isnotempty' => 'ist nicht leer',


/**
 *	Date expressions
 *	----------------
 */

'in_past_day' => 'in den letzten 24 Stunden',

'in_past_week' => 'in den letzten 7 Tagen',

'in_past_month' => 'in den letzten 30 Tagen',

'in_past_six_months' => 'in den letzten 180 Tagen',

'in_past_year' => 'in den letzten 365 Tagen',

'next_day' => 'in den nächsten 24 Stunden',

'next_week' => 'in den nächsten 7 Tagen',

'next_month' => 'in den nächsten 30 Tagen',

'next_six_months' => 'in den nächsten 180 Tagen',

'next_year' => 'in den nächsten 365 Tagen',

'between_these_dates'	=> 'innerhalb dieses Datumbereichs:',


// - index
// 
// 
// 

'showing' => 'Zeige ',

'to' => 'von',

'out_of' => 'aus',

'no_results' => 'Kein Eintrag gefunden.',

'show_images' => 'Zeige Bilder',

'add_this_search_as_tab' => 'Diese Sucheinstellung als Menue-Eintrag speichern',

'add' => 'Zufügen',

'remove' => 'Entfernen',

'add_filter_rule' => 'Filter-Regel anfügen',

'remove_filter_rule' => 'Filter-Regel entfernen',

'last_author' => 'Zuletzt bearbeitet von:',

'saved_searches' => 'Gespeicherte Suchen',

'save_this_search' => 'Diese Suche speichern',

'delete_this_search' => 'Diese Suche speichern',

'give_rule_label' => 'Label für Such-Filter:',

'saved_search' => 'Gespeicherte Suche',

'rapid_loading_error'		=> 'Es trat ein Fehler auf. Wahrscheinlich wegen einem raschen Refresh des Suchformulars. Weil nicht alle Suchfilter Zeit hatten, komplett zu laden, werden die voreingestellten Fileter an Stelle geladen.',

/**
 * 	Error - Warnings
 * 	----------------
 */

'saved_search_delete_warning'	=> 'Are you sure you want to delete this search?',

// - Settings
// 
// 
// 
// 

'display_settings' => 'Anzeige Einstellungen',

'general_settings' => 'Allgemeine Einstellungen',

'max_results_per_page' => 'Eigene Limite für Einträge pro Seite',

'max_results_per_page_note' => 'Wird angefügt zum Dropdown von "Zeige X Ergebnisse". <strong style="color: red">Warnung: </strong>Hohe Werte werden einen Einfluss auf die Abfragegeschwindigkeit haben, die zu längerer Wartezeit bis zur Anzeige oder gar einem Timeout führen kann',

'default_filter' => 'Standard Filter-Regel',

'default_filter_note' => 'Wird als Standard die erste Filter-Regel auf neuen Seiten. (z.B. ohne voreingestellte Filter-Regeln)',

'default_order_sort' => 'Standard Reihenfolge und Sortierung',

'default_order_sort_note' => 'Standard auf neuen Seiten. (z.B. ohne voreingestellte Filter-Regeln))',

'enable_hidden_field_search'		=> 'Alle eigenen Felder durchsuchbar machen',


'enable_hidden_field_search_note'	=> 'Wenn aktiviert werden alle eigenen Felder für die Suche einbezogen, auch wenn sie in der Treffer-Tabelle nicht zur Anzeige kommen.',

'option' => 'Option',

'field' => 'Feld',

'all_channels' => 'alle Channels',

'multi_channel_entries' => 'Multi-channel Eintragsliste',

'or_skip_to' => 'oder springe zu',

'extra_options' => 'Extra Optionen',

'save_settings' => 'Speichere Einstellungen',

'message_settings_saved' => 'Einstellungen erfolgreich gespeichert',

'error_not_numeric' => 'Fehler: Einige Eingaben waren nicht Integer/Zahlen',

'field_order' => 'Feldreihenfolge',

'date_format' => 'Datum-Format',

'date_format_future' => 'Nach der aktuellen Zeit',

'y' => 'Ja',

'n' => 'Nein',

'show_' => 'Zeige ',

'_in_row' => ' in Zeilen',

'warning_channel_fields_no_display' => 'Es wurden keine Felder für die folgenden Channels gewählt:'."\n\n",

'warning_save_confirm' => "\n\n".'Ergebnisse können vielleicht als eine Spalte ohne Checkboxen gezeigt werden. Trotzdem die Einstellungen speichern?',

// - Setting options
// 
// 
// 

'edit_date' => 'Bearbeitungsdatum',

'view_count' => 'Anzeige-Zähler',

'show_view_count' => 'Zeige Anzeige-Zähler',

'show_last_author' => 'Letzter Autor der Bearbeitung',

'show_autosave'				=> 'Zeige autogespeicherte Einträge',

'word_limit' => 'Wort-Limit',

'show_channel_images_cover' => 'Zeige nur Bild-Cover (oder erstes Bild)',

'use_livelook_settings' => 'Benutze die Live Look Einstellungen',

'use_custom_segments' => 'Benutze Custom Segmente (leer = kein Live Look)',

'custom_segments' => 'Segmente:',

'livelook_pages_override' => 'Überschreibe mit Pages URL wenn verfügbar',

'show_html' => 'Zeige HTML Markup im Text',

'no_html' => 'Zeige Text als reinen Text',

'convert_to_regular_number'	=> 'Konvertiere Exponential-Zahlen zu Dezimal',

'number_of_decimals'		=> 'Anzahl Dezimalstellen: ',

'use_thumbnail' => 'Vorschaubild benutzen: ',

'standard_thumbs' => 'Eingestellte EE Vorschaubild-Grösse',

// - Setting for admin
// 
// 
// 
// 
'member_access_settings' => 'Mitglieder-Berechtigungen Einstellungen',

'save_this_profile_for_link' => 'Kopiere dieses Profile zu Mitgliedergruppe &raquo;',

'save_this_profile_for' => 'Dieses Profil auch speichern für die folgende Mitgliedergruppen ...',

'clear_individual_settings' => 'Lösche individuelle Einstellungen für jedes Mitglied der oben angewählten Mitgliedergruppen',

'member_group_name' => 'Name der Mitglieder-Gruppe',

'can_admin' => 'Darf "<strong>Mitglieder-Berechtigungen Einstellungen</strong>" (diese Seite) bearbeiten', // Can administrate member group access

'can_copy_profile' => 'Darf Profile für andere Mitglieder-Gruppen speichern',

'can_access_settings' => 'Kann "<strong>Anzeige Einstellungen</strong>" sehen',

'edit_replace' => 'Modifiziere den Link bei Inhalt &raquo; Bearbeiten',

'edit_replace_desc' => 'Diese Option ändert den Bearbeiten-Link unter Inhalt => Bearbeiten so, dass er sich auf dieses Addon bezieht, d.h. ein Untermenue für jeden Channel wird angefügt',

'replace_links_for_zenbu' => 'Modifiziere Link unter BEARBEITEN für Zenbu',

'enable_module_for'				=> 'Aktiviere Modul für folgende Mitgliedergruppen:',

'enable_module_for_subtext'		=> '<em>HINWEIS:</em> Wird diese Funktion für die folgenden Mitgliedergruppen aktiviert, aktiviert das auch die Menupunkte <strong>ADD-ONS</strong> und <strong>ADD-ONS => Module</strong> in der Hauptnavigation des Control Panels. Das ist eine Erfortdernis von ExpressionEngine damit Zenbu vollständig angesprochen werden kann. Um den Zugriff auf diese Menus im Nauchhinein aus- oder einzuschalten gehen Sie bitte zu den Einstellungen der betreffenden Mitgliedergruppe.',

// Multi-edit
// 
// 
// 
// 
'deleting' => 'Lösche...',

'saving' => 'Speichere...',

'multi_set_all_status_to' => 'Wenn anwendbar, setze auf',

'cancel_and_return'			=> 'Abbruch und zurück zur vorhergehenden Seite',


// - Extension settings
// 
// 
// 
// 
'license' => 'Lizenz',

/**
 * ============================
 * THIRD-PARTY LANGUAGE STRINGS
 * ============================
 */
'show_calendar_only'	=> 'Zeige nur assoziierten Kalender-Name',

//
''=>''
);