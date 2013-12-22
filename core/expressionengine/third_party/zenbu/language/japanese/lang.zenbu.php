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

'zenbu_module_description'	=> 'コントロールパネルからもっとエントリのデータを表示する。',

/**
*	General
*	-------------
*/
'settings'	=> '設定',

'entries'	=> 'エントリー',

'loading'	=> 'ロード中...',

/**
*	=========
*	INDEX
*	=========
*/ 

'any_custom_fields_titles'		=> 'タイトルもしくは基本のカスタムフィールド',

'by_channel'					=> 'チャネルでフィルター',

'by_category'					=> 'カテゴリでフィルター',

'by_author'						=> '入力者（著者）でフィルター',

'by_status'						=> 'ステータスでフィルター',

'all_statuses'					=> '全てのステータス',

'is_sticky'						=> 'スティッキー',

'not_sticky'					=> 'スティッキーではありません',

'sticky_both'					=> 'スティッキーとスティッキーではありません',

'by_entry_date'					=> '･･･の時までのエントリー',

'by_limit'						=> 'エントリーの数',

'by_categories'					=> '全てのカテゴリ',

'entries_with_no_categories'	=> 'カテゴリに指定されていません',

'by_search_in'					=> 'Search in...',

'titles_and_fields'				=> 'タイトルとフィールドの基本的なコンテンツ',

'titles_only'					=> 'タイトル',

'entry_title'					=> 'エントリのタイトル',

'entry_id'						=> 'エントリのID番号',

'focused_field_search'			=> 'フィールドの詳しい検索',

'keyword'						=> "キーワード&nbsp;&nbsp;",

'custom_fields'					=> 'カスタムフィールド',

'autosave'						=> '自動セーブ',

'orderby'						=> "並び：",

'asc'							=> "上がる順",

'desc'							=> "下がる順",

'in'							=> '･･･に次が入っている：',

'not_in'						=> '･･･に次が入っていない：',

'is'							=> 'は',

'isnot'							=> 'は次ではない：',

'contains'						=> 'は次が含まれています：',

'doesnotcontain'				=> 'は次が含まれていません：',

'beginswith'					=>	'は次で始まっています：',

'doesnotbeginwith'				=> 'は次で始まっていません：',

'endswith'						=> 'は次で終わっています：',

'doesnotendwith'				=> 'は次で終わっていません：',

'containsexactly'				=> 'は次がちょうど含まれています：',

'isempty'						=> 'は空です',

'isnotempty'					=> 'は空ではありません',

/**
*	Date expressions
*	----------------
*/
'in_past_day'			=> '過去の24時間',

'in_past_week'			=> '過去の7日間',

'in_past_month'			=> '過去の30日間',

'in_past_six_months'	=> '過去の180日間',

'in_past_year'			=> '過去の365日間',

'next_day'				=> '次の24時間の間',

'next_week'				=> '次の7日間の間',

'next_month'			=> '次の30日間の間',

'next_six_months'		=> '次の180日間の間',

'next_year'				=> '次の365日間の間',


/**
*	Results
*	-------
*/

'showing'					=> '表示する ',

'to'						=> ' - ',

'out_of'					=> ' 合計：',

'no_results'				=> '結果がありません。',

'show_images'				=> '画像を表示する',

'add_this_search_as_tab'	=> 'この検索をメインナビゲーションに追加する',

'add'						=> '追加',

'remove'					=> '削除',

'add_filter_rule'			=> 'フィルターのルールを追加する',

'remove_filter_rule'		=> 'フィルターのルールを削除する',

'last_author'				=> '最後に編集した入力者:',

'saved_searches'			=> '保存された検索',

'save_this_search'			=> 'この検索を保存する',

'delete_this_search'		=> 'この検索を削除する',

'give_rule_label'			=> '検索フィルター名:',

'saved_search'				=> '保存された検索',

/**
 * 	Error - Warnings
 * 	----------------
 */

'saved_search_delete_warning'	=> 'この保存された検索を本当に削除しますか？',

/**
*	===============
*	SETTINGS
*	===============
*/

'display_settings'					=> '表示の設定',

'general_settings'					=> '一般の設定',

'max_results_per_page'				=> '各ページのエントリの数のリミット',

'max_results_per_page_note'			=> '「表示：○の結果」をセレクトフィールドに追加されます。 <strong style="color: red">注意: </strong>高い数値を入力する場合、ケーリのパーフォマンス、エントリを表示する時間、タイムアウトなどに影響がある可能性があります。',

'default_filter'					=> 'フィルターのルールのデフォールト設定',

'default_filter_note'				=> '新ページ（まだフィルターがないページ）に表示する最初のフィルターのルールを設定できます。',

'default_order_sort'				=> '順番とソートのデフォールト設定',

'default_order_sort_note'			=> '新ページ（まだフィルターがないページ）に表示する順番とソートのデフォールトを設定できます。',

'enable_hidden_field_search'		=> '全てのカスタムフィールドを検索に可能にする',

'enable_hidden_field_search_note'	=> 'このオプションを有効にすると、フィールドは検索結果のリストに表示すると設定されなくても、全てのカスタムフィールドを検索のために利用ですます。',

'option'							=> 'オプション',

'field'								=> 'フィールド',

'all_channels'						=> '全てのチャネル',

'multi_channel_entries'				=> 'マルチチャネルのエントリのリスト',

'or_skip_to'						=> 'また次に移動する:',

'extra_options'						=> '追加オプション',

'save_settings'						=> '設定を保存する',

'message_settings_saved'			=> '設定を保存しました',

'error_not_numeric'					=> '半角の数字でない設定がありました。',

'field_order'						=> 'フィールドの順番',

'date_format'						=> '日付フォーマット',

'date_format_future'				=> '現在の日時を過ぎると：',

'y'									=> 'はい',

'n'									=> 'いいえ',

'show_'								=> '表示：',

'_in_row'							=> 'を行に表示する',

'warning_channel_fields_no_display'	=> '次のチャネルは表示するフィールドが選択されていません：'."\n\n",

'warning_save_confirm'				=> "\n\n".'この設定で、エントリのリストはチェックボックスのみが入っている一つだけの欄が表示される場合があります。本当に設定のまま保存しますか？',

'warning_forgot_to_save'			=> 'この設定ページには保存されていないデータがありうます。続きたい場合、このデータは保存されません。' . "\n\n" . '本当に続きますか？（未保存のデータはなくなります）',


/**
*	Setting options
*	---------------
*/

'edit_date'					=> '編集の日付',

'view_count'				=> 'ビューの数',

'show_view_count'			=> 'ビューの数を表示する',

'show_last_author'			=> '最後にエントリを編集した入力者',

'show_autosave'				=> '自動的に保存されたエントリを表示する',

'word_limit'				=> '文字のリミット',

'show_channel_images_cover'	=> 'カバーイメージ（もしくは最初の画像）のみを表示する',

'use_livelook_settings'		=> 'チャネルのLive Lookの設定を使う',

'use_custom_segments'		=> 'カスタムなセグメントを使う (空 = Live Lookなし)',

'custom_segments'			=> 'セグメント:',

'livelook_pages_override'	=> 'Pagesモジュールで設定されているURLがある時に利用する',

'livelook_not_set'			=> '(選択したテンプレートがありません) ',

'show_html'					=> 'HTMLコードをテキストに表示する',

'no_html'					=> 'プレインテキストとして表示する',

'use_thumbnail'				=> '利用するサムネール： ',

'standard_thumbs'			=> 'デフォールトのEEのサムネールサイズ',

/**
*	====================
*	SETTINGS FOR ADMIN
*	====================
*/
'member_access_settings'		=> 'メンバーのアクセスの設定',

'save_this_profile_for_link'	=> 'このプロファイルをメンバーグループにコピーする &raquo;',

'save_this_profile_for'			=> '・・・このプロファイルを次のメンバーグループにコピーする：',

'clear_individual_settings'		=> '上の ＊チェックされている＊ メンバーグループに入っているメンバーの個人設定をクリアーする',

'member_group_name'				=> 'メンバーグループ名',

'can_admin'						=> '「メンバーのアクセスの設定」をアクセスできる', // 他のメンバーグループのアクセスの管理はできる

'can_copy_profile'				=> 'プロファイルを他のメンバーグループに保存ーできる',

'can_access_settings'			=> '「表示の設定」をアクセスできる',

'edit_replace'					=> '「コンテンツ 〉編集」のリンクを変更する',

'edit_replace_desc'				=> 'このオプションは「コンテンツ」=>「編集」のリンクをこのアドンに設定し、更にチャネルのリストから選べるように設定します',

'replace_links_for_zenbu'		=> '編集セクションへのリンクをZenbuに変更する',

'enable_module_for'				=> '次のメンバーグループはモジュールをアクセスできます：',

/**
*	==================
* 	MULTI-ENTRY EDIT
*	==================
*/
'deleting'					=> '削除中...',

'saving'					=> '保存中...',

'multi_set_all_status_to'	=> '可能な場合、次のステータスに変更する',

'cancel_and_return'			=> 'キャンセルして前のページへ戻る',

/**
*	=====================
*	EXTENSION SETTINGS
*	=====================
*/
'license'	=> 'ライセンス番号',

/**
 * ============================
 * THIRD-PARTY LANGUAGE STRINGS
 * ============================
 */
'show_calendar_only'	=> 'カレンダー名のみを表示する',


//
''=>''
);