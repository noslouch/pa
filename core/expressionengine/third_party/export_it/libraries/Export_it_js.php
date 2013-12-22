<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2011, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @version		1.0
 * @filesource 	./system/expressionengine/third_party/export_it/
 */
 
 /**
 * Export It - JavaScript Library Class
 *
 * Contains all the JavaScript
 *
 * @package 	mithra62:Export_it
 * @author		Eric Lamb
 * @filesource 	./system/expressionengine/third_party/export_it/libraries/Export_it_js.php
 */
class Export_it_js
{
	
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	/**
	 * Returns the CSS needed for the Accordions
	 */
	public function get_accordian_css()
	{
		if (version_compare(APP_VER, '2.2', '<') || version_compare(APP_VER, '2.2', '>'))
		{
			return ' $("#my_accordion").accordion({autoHeight: false,header: "h3"}); ';
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * Returns the specialized datatable JS for the member list export view
	 * @param string $ajax_method
	 * @param int $cols
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 * @param string $last_sort
	 */
	public function get_members_datatables($ajax_method, $cols, $piplength, $perpage, $extra = FALSE, $last_sort = FALSE)
	{
				
		$js = '
		var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
			var iPipe = '.$piplength.',
				bNeedServer = false,
				sEcho = fnGetKey(aoData, "sEcho"),
				iRequestStart = fnGetKey(aoData, "iDisplayStart"),
				iRequestLength = fnGetKey(aoData, "iDisplayLength"),
				iRequestEnd = iRequestStart + iRequestLength,
				k_search    = document.getElementById("member_keywords"),
				group_id       = document.getElementById("group_id"),
				date_range       = document.getElementById("date_range");
				f_perpage       = document.getElementById("f_perpage");
				total_range	= document.getElementById("total_range");
		
			function k_search_value() {
				if ($(k_search).data("order_data") == "n") {
					return "";
				}
				
				return k_search.value;
			}		
			aoData.push( 
				{ "name": "k_search", "value": k_search_value() },
				{ "name": "group_id", "value": group_id.value },
				{ "name": "date_range", "value": date_range.value },
				{ "name": "f_perpage", "value": f_perpage.value }
			 );
			
			oCache.iDisplayStart = iRequestStart;
			
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
			{
				bNeedServer = true;
			}
			
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer )
			{
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
				{
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
					{
						if ( aoData[i].value != oCache.lastRequest[i].value )
						{
							bNeedServer = true;
							break;
						}
					}
				}
			}
			
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();
			
			if ( bNeedServer )
			{
				if ( iRequestStart < oCache.iCacheLower )
				{
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 )
					{
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
				
					aoData.push( 
						{ "name": "k_search", "value": k_search_value() },
						{ "name": "group_id", "value": group_id.value },
						{ "name": "date_range", "value": date_range.value },
						{ "name": "f_perpage", "value": f_perpage.value }
					 );
		
				$.getJSON( sSource, aoData, function (json) { 
					/* Callback processing */
					oCache.lastJson = jQuery.extend(true, {}, json);
		 			
					if ( oCache.iCacheLower != oCache.iDisplayStart )
					{
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					
					
					fnCallback(json)
				} );
			}
			else
			{
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}
		var time = new Date().getTime();
	
		oTable = $(".mainTable").dataTable( {	
				"sPaginationType": "full_numbers",
				"bLengthChange": false,
				"bFilter": false,
				"sWrapper": false,
				"sInfo": false,
				"bAutoWidth": false,
				"iDisplayLength": '.$perpage.', 
				'.$extra.'
				
				"aoColumns": [null, null, null, null, null, null ],
				
				
			"oLanguage": {
				"sZeroRecords": "'.lang('no_matching_members').'",
				
				"oPaginate": {
					"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
					"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
				}
			},
			
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method='.$ajax_method.'&time=" + time,
				"fnServerData": fnDataTablesPipeline
		} );

		$("#member_keywords").bind("keydown blur paste", function (e) {
			/* Filter on the column (the index) of this element */
	    	setTimeout(function(){oTable.fnDraw();}, 1);
		});

		$("#export_submit").click(function() {
			var date_range = $("#date_range").val();
			var group_id = $("#group_id").val();
			var include_custom_fields = $("#include_custom_fields").val();
			var complete_select = "0";
			if($("#complete_select").is(":checked"))
			{
				complete_select = "1";
			}
												
			var member_keywords = $("#member_keywords").val();
			var format = $("#export_format").val();
			var dataString = "complete_select="+ complete_select + "&include_custom_fields="+include_custom_fields+ "&format=" +format+ "&date_range="+ date_range + "&group_id=" + group_id + "&member_keywords=" + member_keywords;
			
			window.location.replace(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=export&type=members&"+dataString);	
			return false;
		});
		
		$("#order_form").submit(function() {
			oTable.fnDraw();
  			return false;
		});
		
		$("select#date_range").change(function () {
				
				if($(this).val() == "custom_date")
				{
					function date_range_dt(dateText)
					{

					}
					
					$("#custom_date_picker").slideDown();
					$("#custom_date_end_span").datepicker({
											   altField: "#custom_date_end",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""),
											   onSelect: function(dateText, inst) {
													var start_date = $("#custom_date_start").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+start_date+" to "+dateText+"</option>");
													oTable.fnDraw();
											   }
					});
					$("#custom_date_start_span").datepicker({
											   altField: "#custom_date_start",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""), 
											   defaultDate: new Date($("#custom_date_start").val() != "yy-mm-dd" ? $("#default_start_date").val() : $("#custom_date_start").val()),
											   onSelect: function(dateText, inst) {
													var end_date = $("#custom_date_end").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+dateText+" to "+end_date+"</option>");
													oTable.fnDraw();
											   }								   
					});	

				}
				else
				{
					$("#custom_date_picker").slideUp();
					oTable.fnDraw();
				}
		});		
	
		$("#f_perpage").change(function () {
				oTable.fnDraw();
			});
					
		$("select#group_id").change(function () {
				oTable.fnDraw();
			});

		$("#custom_date_picker").mouseleave(function() {
			$("#custom_date_picker").slideUp();
		});	

		$(".group_filter_id").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("select#group_id").val(replace);
			oTable.fnDraw();
			return false;
		});		
	
		';
		
		return $js;
	}

	/**
	 * Returns the specialized datatable JS for the mailing list export view
	 * @param string $ajax_method
	 * @param int $cols
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 * @param string $last_sort
	 */
	public function get_mailing_list_datatables($ajax_method, $cols, $piplength, $perpage, $extra = FALSE, $last_sort = FALSE)
	{
	
		$js = '
			var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
			var iPipe = '.$piplength.',
			bNeedServer = false,
			sEcho = fnGetKey(aoData, "sEcho"),
			iRequestStart = fnGetKey(aoData, "iDisplayStart"),
			iRequestLength = fnGetKey(aoData, "iDisplayLength"),
			iRequestEnd = iRequestStart + iRequestLength,
			k_search    = document.getElementById("keywords"),
			list_id       = document.getElementById("list_id"),
			f_perpage       = document.getElementById("f_perpage");
			total_range	= document.getElementById("total_range");
			
			function k_search_value() {
				if ($(k_search).data("order_data") == "n") {
					return "";
				}
			
				return k_search.value;
			}
			aoData.push(
			{ "name": "k_search", "value": k_search_value() },
			{ "name": "list_id", "value": list_id.value },
			{ "name": "f_perpage", "value": f_perpage.value }
			);
				
			oCache.iDisplayStart = iRequestStart;
				
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
			{
				bNeedServer = true;
			}
				
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer )
			{
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
				{
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
					{
						if ( aoData[i].value != oCache.lastRequest[i].value )
						{
							bNeedServer = true;
							break;
						}
					}
				}
			}
				
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();
				
			if ( bNeedServer )
			{
				if ( iRequestStart < oCache.iCacheLower )
				{
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 )
					{
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
				
				aoData.push(
				{ "name": "k_search", "value": k_search_value() },
				{ "name": "list_id", "value": list_id.value },
				{ "name": "f_perpage", "value": f_perpage.value }
				);
				
				$.getJSON( sSource, aoData, function (json) {
					/* Callback processing */
					oCache.lastJson = jQuery.extend(true, {}, json);
					if ( oCache.iCacheLower != oCache.iDisplayStart )
					{
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					fnCallback(json)
				} );
			}
			else
			{
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}
		var time = new Date().getTime();
		
		oTable = $(".mainTable").dataTable( {
			"sPaginationType": "full_numbers",
			"bLengthChange": false,
			"bFilter": false,
			"sWrapper": false,
			"sInfo": false,
			"bAutoWidth": false,
			"iDisplayLength": '.$perpage.',
			'.$extra.'
			
			"aoColumns": [null, null, null ],
			
			
			"oLanguage": {
			"sZeroRecords": "'.lang('no_matching_emails').'",
			
			"oPaginate": {
			"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
			"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
			"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
			"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
			}
			},
				
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method='.$ajax_method.'&time=" + time,
			"fnServerData": fnDataTablesPipeline
		} );
		
		$("#keywords").bind("keydown blur paste", function (e) {
			/* Filter on the column (the index) of this element */
			setTimeout(function(){oTable.fnDraw();}, 1);
		});
		
		$("#export_submit").click(function() {
			var date_range = $("#date_range").val();
			var list_id = $("#list_id").val();
			var keywords = $("#keywords").val();
			var format = $("#export_format").val();
			var dataString = "format=" +format+ "&list_id=" + list_id + "&keywords=" + keywords;
				
			window.location.replace(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=export&type=mailinglist&"+dataString);
			return false;
		});
		
		$("#order_form").submit(function() {
			oTable.fnDraw();
			return false;
		});

		$("#f_perpage").change(function () {
			oTable.fnDraw();
		});
			
		$("select#list_id").change(function () {
			oTable.fnDraw();
		});
		
		$(".mailinglist_filter_id").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("select#list_id").val(replace);
			oTable.fnDraw();
			return false;
		});			
	
	';
	
		return $js;
	}
	
	/**
	 * Returns the specialized datatable JS for the comment list export view
	 * @param string $ajax_method
	 * @param int $cols
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 * @param string $last_sort
	 */	
	public function get_comments_datatables($ajax_method, $cols, $piplength, $perpage, $extra = FALSE, $last_sort = FALSE)
	{
				
		$js = '
		var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
			var iPipe = '.$piplength.',
				bNeedServer = false,
				sEcho = fnGetKey(aoData, "sEcho"),
				iRequestStart = fnGetKey(aoData, "iDisplayStart"),
				iRequestLength = fnGetKey(aoData, "iDisplayLength"),
				iRequestEnd = iRequestStart + iRequestLength,
				k_search    = document.getElementById("keywords"),
				channel_id       = document.getElementById("channel_id"),
				status       = document.getElementById("status"),
				date_range       = document.getElementById("date_range");
				f_perpage       = document.getElementById("f_perpage");
				total_range	= document.getElementById("total_range");
		
			function k_search_value() {
				if ($(k_search).data("order_data") == "n") {
					return "";
				}
				
				return k_search.value;
			}		
			aoData.push( 
				{ "name": "k_search", "value": k_search_value() },
				{ "name": "channel_id", "value": channel_id.value },
				{ "name": "status", "value": status.value },
				{ "name": "date_range", "value": date_range.value },
				{ "name": "f_perpage", "value": f_perpage.value }
			 );
			
			oCache.iDisplayStart = iRequestStart;
			
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
			{
				bNeedServer = true;
			}
			
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer )
			{
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
				{
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
					{
						if ( aoData[i].value != oCache.lastRequest[i].value )
						{
							bNeedServer = true;
							break;
						}
					}
				}
			}
			
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();
			
			if ( bNeedServer )
			{
				if ( iRequestStart < oCache.iCacheLower )
				{
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 )
					{
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
				
					aoData.push( 
						{ "name": "k_search", "value": k_search_value() },
						{ "name": "channel_id", "value": channel_id.value },
						{ "name": "status", "value": status.value },
						{ "name": "date_range", "value": date_range.value },
						{ "name": "f_perpage", "value": f_perpage.value }
					 );
		
				$.getJSON( sSource, aoData, function (json) { 
					/* Callback processing */
					oCache.lastJson = jQuery.extend(true, {}, json);
		 			
					if ( oCache.iCacheLower != oCache.iDisplayStart )
					{
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					
					
					fnCallback(json)
				} );
			}
			else
			{
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}
		var time = new Date().getTime();
	
		oTable = $(".mainTable").dataTable( {	
				"sPaginationType": "full_numbers",
				"bLengthChange": false,
				"bFilter": false,
				"sWrapper": false,
				"sInfo": false,
				"bAutoWidth": false,
				"iDisplayLength": '.$perpage.', 
				'.$extra.'
				
				"aoColumns": [null, null, null, null, null, null ],
				
				
			"oLanguage": {
				"sZeroRecords": "'.lang('no_matching_comments').'",
				
				"oPaginate": {
					"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
					"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
				}
			},
			
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method='.$ajax_method.'&time=" + time,
				"fnServerData": fnDataTablesPipeline
		} );

		$("#keywords").bind("keydown blur paste", function (e) {
			/* Filter on the column (the index) of this element */
	    	setTimeout(function(){oTable.fnDraw();}, 1);
		});

		$("#export_submit").click(function() {
			var date_range = $("#date_range").val();
			var channel_id = $("#channel_id").val();
			var keywords = $("#keywords").val();
			var format = $("#export_format").val();
			var status = $("#status").val();
			var dataString = "status="+status+"&format=" +format+ "&date_range="+ date_range + "&channel_id=" + channel_id + "&keywords=" + keywords;
			
			window.location.replace(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=export&type=comments&"+dataString);	
			return false;
		});
		
		$("#order_form").submit(function() {
			oTable.fnDraw();
  			return false;
		});
		
		$("select#date_range").change(function () {
				
				if($(this).val() == "custom_date")
				{
					function date_range_dt(dateText)
					{

					}
					
					$("#custom_date_picker").slideDown();
					$("#custom_date_end_span").datepicker({
											   altField: "#custom_date_end",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""),
											   onSelect: function(dateText, inst) {
													var start_date = $("#custom_date_start").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+start_date+" to "+dateText+"</option>");
													oTable.fnDraw();
											   }
					});
					$("#custom_date_start_span").datepicker({
											   altField: "#custom_date_start",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""), 
											   defaultDate: new Date($("#custom_date_start").val() != "yy-mm-dd" ? $("#default_start_date").val() : $("#custom_date_start").val()),
											   onSelect: function(dateText, inst) {
													var end_date = $("#custom_date_end").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+dateText+" to "+end_date+"</option>");
													oTable.fnDraw();
											   }								   
					});	

				}
				else
				{
					$("#custom_date_picker").slideUp();
					oTable.fnDraw();
				}
		});		
	
		$("#f_perpage").change(function () {
				oTable.fnDraw();
		});
					
		$("select#channel_id").change(function () {
				oTable.fnDraw();
		});
		
		$("select#status").change(function () {
				oTable.fnDraw();
		});		

		$("#custom_date_picker").mouseleave(function() {
			$("#custom_date_picker").slideUp();
		});	

		$(".status_filter_id").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("select#status").val(replace);
			oTable.fnDraw();
			return false;
		});	
		
		$(".keyword_filter_value").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("#keywords").val(replace);
			oTable.fnDraw();
			return false;
		});

		$(".channel_filter_id").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("select#channel_id").val(replace);
			oTable.fnDraw();
			return false;
		});			

		
	
		';
		
		return $js;
	}

	/**
	 * Returns the specialized datatable JS for the comment list export view
	 * @param string $ajax_method
	 * @param int $cols
	 * @param int $piplength
	 * @param int $perpage
	 * @param string $extra
	 * @param string $last_sort
	 */	
	public function get_channel_entries_datatables($ajax_method, $cols, $piplength, $perpage, $extra = FALSE, $last_sort = FALSE)
	{
				
		$js = '
		var oCache = {
			iCacheLower: -1
		};
		
		function fnSetKey( aoData, sKey, mValue )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					aoData[i].value = mValue;
				}
			}
		}
		
		function fnGetKey( aoData, sKey )
		{
			for ( var i=0, iLen=aoData.length ; i<iLen ; i++ )
			{
				if ( aoData[i].name == sKey )
				{
					return aoData[i].value;
				}
			}
			return null;
		}
		
		function fnDataTablesPipeline ( sSource, aoData, fnCallback ) {
			var iPipe = '.$piplength.',
				bNeedServer = false,
				sEcho = fnGetKey(aoData, "sEcho"),
				iRequestStart = fnGetKey(aoData, "iDisplayStart"),
				iRequestLength = fnGetKey(aoData, "iDisplayLength"),
				iRequestEnd = iRequestStart + iRequestLength,
				k_search    = document.getElementById("keywords"),
				channel_id       = document.getElementById("channel_id"),
				category = document.getElementById("category"),
				status       = document.getElementById("status"),
				date_range       = document.getElementById("date_range");
				f_perpage       = document.getElementById("f_perpage");
				total_range	= document.getElementById("total_range");
		
			function k_search_value() {
				if ($(k_search).data("order_data") == "n") {
					return "";
				}
				
				return k_search.value;
			}		
			aoData.push( 
				{ "name": "k_search", "value": k_search_value() },
				{ "name": "channel_id", "value": channel_id.value },
				{ "name": "status", "value": status.value },
				{ "name": "category", "value": category.value },
				{ "name": "date_range", "value": date_range.value },
				{ "name": "f_perpage", "value": f_perpage.value }
			 );
			
			oCache.iDisplayStart = iRequestStart;
			
			/* outside pipeline? */
			if ( oCache.iCacheLower < 0 || iRequestStart < oCache.iCacheLower || iRequestEnd > oCache.iCacheUpper )
			{
				bNeedServer = true;
			}
			
			/* sorting etc changed? */
			if ( oCache.lastRequest && !bNeedServer )
			{
				for( var i=0, iLen=aoData.length ; i<iLen ; i++ )
				{
					if ( aoData[i].name != "iDisplayStart" && aoData[i].name != "iDisplayLength" && aoData[i].name != "sEcho" )
					{
						if ( aoData[i].value != oCache.lastRequest[i].value )
						{
							bNeedServer = true;
							break;
						}
					}
				}
			}
			
			/* Store the request for checking next time around */
			oCache.lastRequest = aoData.slice();
			
			if ( bNeedServer )
			{
				if ( iRequestStart < oCache.iCacheLower )
				{
					iRequestStart = iRequestStart - (iRequestLength*(iPipe-1));
					if ( iRequestStart < 0 )
					{
						iRequestStart = 0;
					}
				}
				
				oCache.iCacheLower = iRequestStart;
				oCache.iCacheUpper = iRequestStart + (iRequestLength * iPipe);
				oCache.iDisplayLength = fnGetKey( aoData, "iDisplayLength" );
				fnSetKey( aoData, "iDisplayStart", iRequestStart );
				fnSetKey( aoData, "iDisplayLength", iRequestLength*iPipe );
				
					aoData.push( 
						{ "name": "k_search", "value": k_search_value() },
						{ "name": "channel_id", "value": channel_id.value },
						{ "name": "status", "value": status.value },
						{ "name": "category", "value": category.value },
						{ "name": "date_range", "value": date_range.value },
						{ "name": "f_perpage", "value": f_perpage.value }
					 );
		
				$.getJSON( sSource, aoData, function (json) { 
					/* Callback processing */
					oCache.lastJson = jQuery.extend(true, {}, json);
		 			
					if ( oCache.iCacheLower != oCache.iDisplayStart )
					{
						json.aaData.splice( 0, oCache.iDisplayStart-oCache.iCacheLower );
					}
					json.aaData.splice( oCache.iDisplayLength, json.aaData.length );
					
					
					fnCallback(json)
				} );
			}
			else
			{
				json = jQuery.extend(true, {}, oCache.lastJson);
				json.sEcho = sEcho; /* Update the echo for each response */
				json.aaData.splice( 0, iRequestStart-oCache.iCacheLower );
				json.aaData.splice( iRequestLength, json.aaData.length );
				fnCallback(json);
				return;
			}
		}
		var time = new Date().getTime();
	
		oTable = $(".mainTable").dataTable( {	
				"sPaginationType": "full_numbers",
				"bLengthChange": false,
				"bFilter": false,
				"sWrapper": false,
				"sInfo": false,
				"bAutoWidth": false,
				"iDisplayLength": '.$perpage.', 
				'.$extra.'
				
				"aoColumns": [null, null, null, null, null ],
				
				
			"oLanguage": {
				"sZeroRecords": "'.lang('no_matching_channel_entries').'",
				
				"oPaginate": {
					"sFirst": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sPrevious": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />",
					"sNext": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />", 
					"sLast": "<img src=\"'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif\" width=\"13\" height=\"13\" alt=\"&lt; &lt;\" />"
				}
			},
			
				"bProcessing": true,
				"bServerSide": true,
				"sAjaxSource": EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method='.$ajax_method.'&time=" + time,
				"fnServerData": fnDataTablesPipeline
		} );

		$("#keywords").bind("keydown blur paste", function (e) {
			/* Filter on the column (the index) of this element */
	    	setTimeout(function(){oTable.fnDraw();}, 1);
		});

		$("#export_submit").click(function() {
			var date_range = $("#date_range").val();
			var channel_id = $("#channel_id").val();
			var keywords = $("#keywords").val();
			var format = $("#export_format").val();
			var status = $("#status").val();
			var category = $("#category").val();
			var complete_select = "0";
			if($("#complete_select").is(":checked"))
			{
				complete_select = "1";
			}
			
			var dataString = "status="+status+"&category=" + category + "&complete_select=" + complete_select + "&format=" +format+ "&date_range="+ date_range + "&channel_id=" + channel_id + "&keywords=" + keywords;
			if(channel_id == "0")
			{
				alert("Please select a channel first");
			}
			else
			{
				window.location.replace(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=export&type=channel_entries&"+dataString);	
			}
			
			return false;
		});
		
		$("#order_form").submit(function() {
			oTable.fnDraw();
  			return false;
		});
		
		$("select#date_range").change(function () {
				
				if($(this).val() == "custom_date")
				{
					function date_range_dt(dateText)
					{

					}
					
					$("#custom_date_picker").slideDown();
					$("#custom_date_end_span").datepicker({
											   altField: "#custom_date_end",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""),
											   onSelect: function(dateText, inst) {
													var start_date = $("#custom_date_start").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+start_date+" to "+dateText+"</option>");
													oTable.fnDraw();
											   }
					});
					$("#custom_date_start_span").datepicker({
											   altField: "#custom_date_start",
											   altFormat: "yy-mm-dd",
											   dateFormat: "yy-mm-dd",
											   maxDate: new Date,
											   minDate: new Date($("#default_start_date").val() != "" ? $("#default_start_date").val() : ""), 
											   defaultDate: new Date($("#custom_date_start").val() != "yy-mm-dd" ? $("#default_start_date").val() : $("#custom_date_start").val()),
											   onSelect: function(dateText, inst) {
													var end_date = $("#custom_date_end").val();
													var check = $("#custom_date_option").remove();
													$("#date_range").append(\'<option id="custom_date_option" selected="selected">\'+dateText+" to "+end_date+"</option>");
													oTable.fnDraw();
											   }								   
					});	

				}
				else
				{
					$("#custom_date_picker").slideUp();
					oTable.fnDraw();
				}
		});		
	
		$("#f_perpage").change(function () {
				oTable.fnDraw();
		});
					
		$("select#channel_id").change(function () {
			
			var channel = $(this).val();
			$("select#status option").each(function(i, option){ $(option).remove(); });
			$.getJSON(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=channel_options_ajax_filter&option_type=status&channel_id="+channel+"&time=" + time, function(data) {
			
				$.each(data, function(val, text) {
					
				    $("select#status").append(
				        $("<option></option>").val(val).html(text)
				    );
				});
			
			});
						

			$("select#category option").each(function(i, option){ $(option).remove(); });
			$.getJSON(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=channel_options_ajax_filter&option_type=category&channel_id="+channel+"&time=" + time, function(data) {
			
				$.each(data, function(val, text) {
					
				    $("select#category").append(
				        $("<option></option>").val(val).html(text)
				    );
				});
			
			});						
												
			oTable.fnDraw();
		});
		
		$("select#status").change(function () {
				oTable.fnDraw();
		});

		$("select#category").change(function () {
				oTable.fnDraw();
		});						

		$(".channel_filter_id").live("click", function(){ 

			var channel_id = $(this).attr("rel");
			$("select#channel_id").val(channel_id);

			$("select#status option").each(function(i, option){ $(option).remove(); });
			$.getJSON(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=channel_options_ajax_filter&option_type=status&channel_id="+channel_id+"&time=" + time, function(data) {
			
				$.each(data, function(val, text) {
					
				    $("select#status").append(
				        $("<option></option>").val(val).html(text)
				    );
				});
			
			});
						

			$("select#category option").each(function(i, option){ $(option).remove(); });
			$.getJSON(EE.BASE+"&C=addons_modules&M=show_module_cp&module=export_it&method=channel_options_ajax_filter&option_type=category&channel_id="+channel_id+"&time=" + time, function(data) {
			
				$.each(data, function(val, text) {
					
				    $("select#category").append(
				        $("<option></option>").val(val).html(text)
				    );
				});
			
			});	
												
			oTable.fnDraw();
			return false;
		});
		
		$(".status_filter_id").live("click", function(){ 

			var status = $(this).attr("rel");
			$("select#status").val(status);
			oTable.fnDraw();
			return false;
		});
		
		$(".keyword_filter_value").live("click", function(){ 

			var replace = $(this).attr("rel");
			$("#keywords").val(replace);
			oTable.fnDraw();
			return false;
		});

		$("#custom_date_picker").mouseleave(function() {
			$("#custom_date_picker").slideUp();
		});			
	
		';
		
		return $js;
	}
}