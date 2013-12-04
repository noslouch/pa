<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*	===============================
*	ZENBU LANGUAGE FILE
*	===============================
*	The Zenbu addon uses strings from the core ExpressionEngine language files,
*	in addition to the languages strings below
*
*/

$lang = array(

'zenbu_module_name'			=> 'Zenbu',

'zenbu_module_description'	=> 'Affiche plus de données dans votre liste d\'entrées dans le panneau de contrôle',

/**
*	General
*	-------------
*/
'settings'	=> 'Configuration',

'entries'	=> 'Entrées',

'loading'	=> 'Chargement en cours...',

/**
*	=========
*	INDEX
*	=========
*/ 

'any_custom_fields_titles'		=> 'N\'importe quel titre ou champ personalisé de base',

'by_channel'					=> 'Tous les canaux',

'by_category'					=> 'Toutes les catégories',

'by_author'						=> 'Tous les auteurs',

'by_status'						=> 'Tous les status',

'all_statuses'					=> 'Tous les status',

'is_sticky'						=> 'Collant',

'not_sticky'					=> 'Pas collant',

'sticky_both'					=> 'Collants et pas collants',

'by_entry_date'					=> 'dans des dernières...',

'by_limit'						=> 'Nombre d\'entrées',

'by_categories'					=> 'Toutes les catégories',

'entries_with_no_categories'	=> 'Pas de catégories',

'by_search_in'					=> 'Rechercer dans...',

'titles_and_fields'				=> 'Titre et contenu de base des champs',

'titles_only'					=> 'Titre',

'entry_title'					=> 'Titre de l\'entrée',

'entry_id'						=> 'Numéro d\'entrée',

'id'							=> '#',

'focused_field_search'			=> 'Recherche détaillée des champs',

'keyword'						=> "Mots clés&nbsp;&nbsp;",

'custom_fields'					=> 'Champs personnalisés',

'autosave'						=> 'Sauvegarde automatique',

'orderby'						=> "Classer par",

'asc'							=> "Ordre montant",

'desc'							=> "Ordre descendant",

'in'							=> 'contient',

'not_in'						=> 'ne contient pas',

'is'							=> 'est',

'isnot'							=> 'n\'est pas',

'contains'						=> 'contient',

'doesnotcontain'				=> 'ne contient pas',

'beginswith'					=> 'commence par',

'doesnotbeginwith'				=> 'ne commence pas par',

'endswith'						=> 'finit par',

'doesnotendwith'				=> 'ne finit pas par',

'containsexactly'				=> 'contient exactement',

'isempty'						=> 'est vide',

'isnotempty'					=> 'n\'est pas vide',

/**
*	Date expressions
*	----------------
*/
'in_past_day'			=> 'dans les dernières 24 heures',

'in_past_week'			=> 'dans les derniers 7 jours',

'in_past_month'			=> 'dans les derniers 30 jours',

'in_past_six_months'	=> 'dans les derniers 180 jours',

'in_past_year'			=> 'dans les derniers 365 jours',

'next_day'				=> 'd\'ici les prochaines 24 heures',

'next_week'				=> 'd\'ici les prochains 7 jours',

'next_month'			=> 'd\'ici les prochains 30 jours',

'next_six_months'		=> 'd\'ici les prochains 180 jours',

'next_year'				=> 'd\'ici les prochains 365 jours',

/**
*	Results
*	-------
*/

'showing'					=> '',

'to'						=> 'à',

'out_of'					=> 'parmi ',

'no_results'				=> 'Aucun résultat trouvé.',

'show_images'				=> 'Afficher les images',

'add_this_search_as_tab'	=> 'Ajouter cette recherche à la barre de navigation',

'add'						=> 'Ajouter',

'remove'					=> 'Supprimer',

'add_filter_rule'			=> 'Ajouter une règle de filtration des entrées',

'remove_filter_rule'		=> 'Supprimer une règle de filtration des entrées',

'last_author'				=> 'Dernièrement édité par:',

'saved_searches'			=> 'Recherches sauvegardée',

'save_this_search'			=> 'Sauvegarder cette recherche',

'delete_this_search'		=> 'Supprimer cette recherche',

'give_rule_label'			=> 'Nom pour ce filtre de recherche:',

'saved_search'				=> 'Recherche sauvegardée',

/**
 * 	Error - Warnings
 * 	----------------
 */

'saved_search_delete_warning'	=> 'Êtes-vous sûr(e) que vous voulez supprimer cette recherche?',

/**
*	===============
*	SETTINGS
*	===============
*/

'display_settings'					=> 'Configuration de l\'affichage',

'general_settings'					=> 'Configuration générale',

'max_results_per_page'				=> 'Limite personalisée d\'entrées par page',

'max_results_per_page_note'			=> 'Ajoutée à la sélection "Afficher X résultats". <strong style="color: red">Avertissement: </strong>Une valeur élevée peut affecter la performance, les résultats peuvent prendre plus de temps ou même dépasser le temps maximal pour la recherche (timeout)',

'default_filter'					=> 'Règle de filtration par défaut',

'default_filter_note'				=> 'La règle de filtration d\'entrée par défaut sur de nouvelles pages (eg. sans filtres pré-établis)',

'default_order_sort'				=> 'Ordre par défaut',

'default_order_sort_note'			=> 'Ordre d\'entrées par défaut sur de nouvelles pages (eg. sans filtres pré-établis)',

'enable_hidden_field_search'		=> 'Rendre disponible tous les champs personalisés pour des recherches',

'enable_hidden_field_search_note'	=> 'Une fois cette option activée, tous les champs personalisés peuvent être utilisés dans une recherche, même s\'ils sont réglés pour ne pas être affichés dans la table de resultats.',

'option'							=> 'Option',

'field'								=> 'Champ',

'all_channels'						=> 'Tous les canaux',

'multi_channel_entries'				=> 'Liste d\'entrées pour multiples canaux',

'or_skip_to'						=> 'ou sauter à',

'extra_options'						=> 'Options supplémentaires',

'save_settings'						=> 'Sauvegarder la configuration',

'message_settings_saved'			=> 'Configuration sauvegardée',

'error_not_numeric'					=> 'Erreur: Certaines valeurs entrées n\'étaient pas des nombres entiers.',

'field_order'						=> 'Ordre des champs',

'date_format'						=> 'Format de date',

'date_format_future'				=> 'Après l\'heure présente',

'y'									=> 'Oui',

'n'									=> 'Non',

'show_'								=> 'Afficher ',

'_in_row'							=> ' dans la rangée',

'warning_channel_fields_no_display'	=> 'Aucun champ n\'a été sélectionné pour les canaux suivants:'."\n\n",

'warning_save_confirm'				=> "\n\n".'Certains résultats risquent d\'être affichés en une colonne contenant seulement des boîtes à cocher. Voulez-vous toujours sauvegarder votre configuration?',

'warning_forgot_to_save'			=> 'Vous avez fait des changements sur cette page de configuration qui n\'ont pas été sauvegardés. Si vous continuez vous perdrez ces données non-sauvegardées. ' . "\n\n" . 'Voulez-vous quand même continuer et abandonner ces chagements?',


/**
*	Setting options
*	---------------
*/

'edit_date'					=> 'Date d\'édition',

'view_count'				=> 'Compteur de vues',

'show_view_count'			=> 'Afficher le compteur de vues',

'show_last_author'			=> 'Dernier auteur ayant édité l\'entrée',

'show_autosave'				=> 'Afficher les entrées autosauvegardées',

'word_limit'				=> 'Limite de mots',

'show_channel_images_cover'	=> 'Afficher l\'image couverture seulement (ou la première image)',

'use_livelook_settings'		=> 'Utiliser la configuration Live Look pour le canal',

'use_custom_segments'		=> 'Utiliser des segments personnalisés (blanc = Pas de Live Look)',

'custom_segments'			=> 'Segments:',

'livelook_pages_override'	=> 'Utiliser plutôt l\'URL du module Pages lorsque disponible',

'livelook_not_set'			=> '(aucun template sélectionné) ',

'show_html'					=> 'Afficher le HTML dans le texte',

'no_html'					=> 'Afficher le texte sans formattage/HTML',

'use_thumbnail'				=> 'Utiliser ce thumbnail: ',

'standard_thumbs'			=> 'Grandeur du thumbnail EE par défaut',

/**
*	====================
*	SETTINGS FOR ADMIN
*	====================
*/
'member_access_settings'		=> 'Configuration d\'accės des membres',

'save_this_profile_for'			=> 'Sauvegarder ce profil pour...
',
'save_this_profile_for_link'	=> 'Copier ce profil à des groupes de members &raquo;',

'save_this_profile_for'			=> '... copier ce profil aux groupes de members suivants:',

'clear_individual_settings'		=> 'Effacer les configurations individuelles pour chaque membre faisant partie des groupes de members *cochés* ci-dessus',

'member_group_name'				=> 'Nom du groupe de membres',

'can_admin'						=> 'Peut accéder à la ≪Configuration d\'accės des membres≫',

'can_copy_profile'				=> 'Peut sauvegarder un profil à d\'autres groupes de membres',

'can_access_settings'			=> 'Peut accéder la ≪Configuration de l\'affichage≫',

'edit_replace'					=> 'Modifier le lien ≪Contenu => Edition≫',

'edit_replace_desc'				=> 'Cette option modifie le lien "Edition" dans "Contenu => Edition" pour pointer vers ce addon, ainsi qu\'ajoute un sous-menu pour chaque canal accessible',

'replace_links_for_zenbu'		=> 'Remplacer les liens vers la section Edition pour Zenbu',

'enable_module_for'				=> 'Activer le module pour les groupes de membres suivants:',

/**
*	==================
* 	MULTI-ENTRY EDIT
*	==================
*/
'deleting'					=> 'Suppression en cours...',

'saving'					=> 'Sauvegarde en cours...',

'multi_set_all_status_to'	=> 'Où applicable, changer pour',

'cancel_and_return'			=> 'Annuler et retourner à la page précédente',


/**
*	=====================
*	EXTENSION SETTINGS
*	=====================
*/
'license'	=> 'Numéro de license',

/**
 * ============================
 * THIRD-PARTY LANGUAGE STRINGS
 * ============================
 */
'show_calendar_only'	=> 'N\'afficher que le nom du calendier associé',

//
''=>''
);