<?php
class BQNotes
{
	public static function add_inline_styles ()
	{
		$plugin_url = plugins_url ();
		ob_start ();
		include dirname (__FILE__).'/font.css.php';
		$font_css = ob_get_clean ();
		echo "\n".'<style type="text/css">'."\n".$font_css."\n</style>\n";
	}

	protected static function get_offset_html ($offset)
	{
		// construct the html for the offset
		$offset_html = '';
		if ($offset != 0)
		{
			// try using inline styles even though the @font-face won't be available
			// this would require the user to have a font installed that supports musical notes
			if (is_feed ())
			{
				$note_accent_span = '<span style="line-height:120%;font-size:80%;vertical-align:50%;">';
				$sharp_symbol_span = '<span style="line-height:130%;font-size:120%;vertical-align:24%;">';
			}
			else
			{
				$note_accent_span = '<span class="note_accent">';
				$sharp_symbol_span = '<span class="sharp_symbol">';
			}

			if ($offset == 2)
				$offset_html = $sharp_symbol_span.'𝄪';
			else
			{
				$offset_html = $note_accent_span;
				switch ($offset)
				{
					case -3: $offset_html .= '♭<span style="margin-left:-1px;">𝄫</span>'; break;
					case -2: $offset_html .= '𝄫'; break;
					case -1: $offset_html .= '♭'; break; // &#x266d;
					case 1: $offset_html .= '♯'; break;
//					case 2: $offset_html .= '</span>'.$sharp_symbol_span.'𝄪'; break;
					case 3: $offset_html .= '♯</span>'.$sharp_symbol_span.'𝄪'; break;
				}
			}
			$offset_html .= '</span>';
		}

		return $offset_html;
	}

	protected function split_shortcode ($content, $shortcode)
	{
		$curpos = 0;
		$open_sc = '['.$shortcode;
		$parts = array ();

		while ($curpos < strlen ($content))
		{
			$start_pos = strpos ($content, $open_sc, $curpos);
			if ($start_pos === false)
			{
				// this is the last section without the shortcode
				$part = substr ($content, $curpos);
				if (strlen ($part) > 0)
					$parts [] = array ('content' => $part, 'in_shortcode' => false);
				$curpos = strlen ($content);
			}
			else
			{
				$end_pos = strpos ($content, ']', $start_pos);
				if ($end_pos === false)
				{
					// an unterminated shortcode
					$parts [] = array ('content' => substr ($content, $curpos), 'in_shortcode' => true);
					$curpos = strlen ($content);
				}
				else
				{
					$part = substr ($content, $curpos, $start_pos - $curpos);
					if (strlen ($part) > 0)
					$parts [] = array ('content' => $part, 'in_shortcode' => false);
					$parts [] = array ('content' => substr ($content, $start_pos, ($end_pos + 1) - $start_pos), 'in_shortcode' => true);
					$curpos = $end_pos + 1;
				}				
			}
		}

		return $parts;
	}

	public static function replace_note_text ($content)
	{
		// could make this a setting, currently off for feeds by default
		if (is_feed ())
			return $content;

		// get the parts of the content to convert or not convert
		$parts = array ();

		$off_parts = preg_split ('/\[bqoff\]/', $content);
		$off_part_count = count ($off_parts);
		if ($off_part_count == 1) // plugin is never switched off
		{
			$sc_parts = self::split_shortcode ($off_parts [0], 'bqchord');
			foreach ($sc_parts as $sc)
				$parts [] = array ('content' => str_replace ('[bqon]', '', $sc ['content']), 'convert' => !$sc ['in_shortcode']);
		}
		else
		{
			for ($p = 0; $p < $off_part_count; $p++)
			{
				if (strlen ($off_parts [$p]) == 0)
					continue;

				// first part is always on
				if ($p == 0)
				{
					// we don't want to change anything in the bqchord shortcode, especially the title
					// because it is probably enclosed in double quotes and the replacement text contains double quotes
					// the bqchord shortcode handler will manually convert its own title
					$sc_parts = self::split_shortcode ($off_parts [$p], 'bqchord');
					foreach ($sc_parts as $sc)
						$parts [] = array ('content' => str_replace ('[bqon]', '', $sc ['content']), 'convert' => !$sc ['in_shortcode']);
				}
				else
				{
					$on_parts = preg_split ('/\[bqon\]/', $off_parts [$p]);
					$on_part_count = count ($on_parts);
					if ($on_part_count == 1) // plugin is never switched on
						$parts [] = array ('content' => $on_parts [0], 'convert' => false); // subsequent parts are always off
					else
					{
						for ($i = 0; $i < $on_part_count; $i++)
						{
							if (strlen ($on_parts [$i]) > 0)
							{
								// first part is always off, subsequent parts are always on
								if ($i == 0)
									$parts [] = array ('content' => $on_parts [$i], 'convert' => false);
								else
								{
									$sc_parts = self::split_shortcode ($on_parts [$i], 'bqchord');
									foreach ($sc_parts as $sc)
										$parts [] = array ('content' => str_replace ('[bqon]', '', $sc ['content']), 'convert' => !$sc ['in_shortcode']);
								}
							}
						}
					}
				}
			}
		}

		// prepare data for the conversion
		$note_patterns = '\A[A-G]|[^a-z^A-Z][A-G]';
		$interval_patterns = '\A[1-7]|[^0-7][1-7]|9|11|13';

		$patterns = "($note_patterns|$interval_patterns)";

		$triple_flat = self::get_offset_html (-3);
		$double_flat = self::get_offset_html (-2);
		$flat = self::get_offset_html (-1);
		$triple_sharp = self::get_offset_html (3);
		$double_sharp = self::get_offset_html (2);
		$sharp = self::get_offset_html (1);

		$result = '';
		foreach ($parts as $part)
		{
			if ($part ['convert'])
			{
				$content_part = $part ['content'];

				$content_part = preg_replace ('/'.$patterns.'(bbb| [Tt]riple [Ff]lat)/', '\1'.$triple_flat, $content_part);
				$content_part = preg_replace ('/'.$patterns.'(bb)([^a-z^A-Z])/', '\1'.$double_flat.'\3', $content_part);
				$content_part = preg_replace ('/'.$patterns.'(bb)([^a-z^A-Z])/', '\1'.$double_flat.'\3', $content_part);
				$content_part = preg_replace ('/'.$patterns.'( [Dd]ouble [Ff]lat)/', '\1'.$double_flat, $content_part);
				$content_part = preg_replace ('/'.$patterns.'(b)([^a-z^A-Z])/', '\1'.$flat.'\3', $content_part);
				$content_part = preg_replace ('/'.$patterns.'(b)([^a-z^A-Z])/', '\1'.$flat.'\3', $content_part);
				$content_part = preg_replace ('/'.$patterns.'( [Ff]lat)/', '\1'.$flat, $content_part);
				$content_part = preg_replace ('/'.$patterns.'(###| [Tt]riple [Ss]harp)/', '\1'.$triple_sharp, $content_part);
				$content_part = preg_replace ('/'.$patterns.'(##| [Dd]ouble [Ss]harp)/', '\1'.$double_sharp, $content_part);
				$content_part = preg_replace ('/'.$patterns.'(#| [Ss]harp)/', '\1'.$sharp, $content_part);

				$result .= $content_part;
			}
			else
				$result .= $part ['content'];
		}

		return $result;
	}

	public static function bq_chord ($atts, $content, $tag)
	{
		// remove the shortcode for feeds
//		if (is_feed ())
//			return '';

		if (isset ($atts ['title']))
			$chord_title = self::replace_note_text ($atts ['title']);
		$fingering = explode (',', $atts ['fingering']);
		for ($f = 0; $f < count ($fingering); $f++)
		{
			if (!is_numeric ($fingering [$f]))
				$fingering [$f] = -1;
		}

		ob_start ();
		include dirname (__FILE__).'/chord_diagram.html.php';
		return ob_get_clean ();
	}
}
?>
