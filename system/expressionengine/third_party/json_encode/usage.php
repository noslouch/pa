This plugin encapsulates PHP's json_encode function.
http://php.net/manual/en/function.json-encode.php

This plugin is useful when making JSON feed templates in EE.

Let's say we are making a feed that contains the 10 most recent blog posts.  Our template would look like:

------------------------------------------------

{
	"posts": [
	{exp:channel:entries channel="blog" status="open" dynamic="no" limit="10" orderby="date" sort="desc" show_future_entries="no" }
		{ "title": {exp:json_encode}{title}{/exp:json_encode}, "summary": {exp:json_encode}{summary}{/exp:json_encode}, "page_url": "{page_url}" }{if count<absolute_results}, {/if}
	{/exp:channel:entries}
	]
}

------------------------------------------------

Optionally, one may set the options parameter of json_encode as follows:

------------------------------------------------

{exp:json_encode options="JSON_UNESCAPED_UNICODE"}{title}{/exp:json_encode}

------------------------------------------------