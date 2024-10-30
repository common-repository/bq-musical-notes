<?php
	// prevent anyone from downloading this stylesheet without a proper url
	if (!isset ($plugin_url))
		die;
?>
/* BQ Musical Notes Wordpress plugin. http://BQPlugins.com/bq-musical-notes/ */
@font-face
{
	font-family: Quivira;
	src: url(<?php echo $plugin_url; ?>/bq-musical-notes/res/quivira-webfont.eot);
	src: local("☺"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/quivira-webfont.woff) format("woff"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/quivira-webfont.ttf) format("truetype"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/quivira-webfont.svg#webfontXmMbIhV) format("svg");
}

@font-face
{
	font-family: DejaVuSansCondensed;
	src: url(<?php echo $plugin_url; ?>/bq-musical-notes/res/dejavusanscondensed-webfont.eot);
	src: local("☺"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/dejavusanscondensed-webfont.woff) format("woff"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/dejavusanscondensed-webfont.ttf) format("truetype"),
			url(<?php echo $plugin_url; ?>/bq-musical-notes/res/dejavusanscondensed-webfont.svg#webfontDFHYUyAX) format("svg");
}

.note_accent
{
	font-family: Quivira;
	font-size: 80%;
	line-height: 120%;
	vertical-align: 50%;
}

.sharp_symbol
{
	font-family: Quivira;
	font-size:120%;
	line-height:80%;
	vertical-align:30%;
}

