<?php
class BQAutoBR
{
	protected static function get_open_tag ($content, $start)
	{
		$start_pos = $start - 1;
		do
		{
			$start_pos = strpos ($content, '<', $start_pos + 1);
			if ($start_pos === false)
				return null; // no more tags to find

			// there must be at least one alphabetic character immediately after the <
			$ord_start_char = ord ($content [$start_pos + 1]);
			if (!($ord_start_char >= ord ('a') && $ord_start_char <= ord ('z')) &&
				!($ord_start_char >= ord ('A') && $ord_start_char <= ord ('Z')))
				continue;

			// there must be a closing tag or space after the tag name
			$gt_pos = strpos ($content, '>', $start_pos); // it might not have attributes
			if ($gt_pos === false)
				return null; // no closing tags left in the content, no more tags to find
			$end_pos = $gt_pos;

			$space_pos = strpos ($content, ' ', $start_pos); // it might have attributes
			$end_name_pos = ($space_pos < $gt_pos) ? $space_pos : $gt_pos;
			$tag_name = substr ($content, $start_pos + 1, ($end_name_pos - 1) - $start_pos);

			return array ('start' => $start_pos, 'name' => $tag_name, 'end' => $end_pos, 'self_closing' => strcmp ($content [$end_pos - 1], '/') == 0);
		}
		while ($start_pos < strlen ($content)); 
	}

	protected static function get_close_tag ($content, $start, $tag_name)
	{
		$close_tags = array ();
		$close_tag = '</'.$tag_name.'>';
		$search = $start;
		while ($search !== false)
		{
			$search = strpos ($content, $close_tag, $search);
			if ($search !== false)
			{
				$close_tags [] = $search;
				$search += strlen ($close_tag);
			}
		}

		$open_tags = array ();
		$open_tag = '<'.$tag_name;
		$search = $start;
		while ($search !== false)
		{
			$search = strpos ($content, $open_tag, $search);
			if ($search !== false)
			{
				// find out if it's self-closing first
				$ot_test = self::get_open_tag ($content, $search);
				if (!$ot_test ['self_closing'])
					$open_tags [] = $search;
				$search += strlen ($open_tag);
			}
		}

		// there should be one more closing tag than opening tags
		if (count ($close_tags) - count ($open_tags) < 1)
			return null;

		$ct_index = 0;
		foreach ($open_tags as $ot)
		{
			if ($ot < $close_tags [0])
				++$ct_index;
			else
				break;
		}

		return array ('start' => $close_tags [$ct_index], 'end' => $close_tags [$ct_index] + (strlen ($close_tag) - 1), 'name' => $tag_name);
	}

	// removing the <p> tags because some browsers don't like inline-block divs inside of p elements
	// replace the <p> tags with something almost as good
	// this will remove all css styles assoicated with <p>
	public static function autobr ($content)
	{
		$content_part = array ();

		$search_pos = $start_pos = 0;
		while ($search_pos < strlen ($content))
		{
			$open_tag_pos = self::get_open_tag ($content, $search_pos);
			if ($open_tag_pos !== null)
			{
				// the tag found is self-closing
				if ($open_tag_pos ['self_closing'])
				{
					if ($open_tag_pos ['start'] > $start_pos) // skip it if it is empty
//{
					$content_part [] = array ('start' => $start_pos, 'end' => $open_tag_pos ['start'] - 1, 'autobr' => true);
//$content_part [] = '[['.str_replace ('<', '&lt;', substr ($content, $start_pos, (($open_tag_pos ['start'] - 1) - $start_pos) + 1)).']]';
//}
					$content_part [] = array ('start' => $open_tag_pos ['start'], 'end' => $open_tag_pos ['end'], 'autobr' => false);
//$content_part [] = '[['.str_replace ('<', '&lt;', substr ($content, $open_tag_pos ['start'], ($open_tag_pos ['end'] - $open_tag_pos ['start']) + 1)).']]';
					$search_pos = $start_pos = $open_tag_pos ['end'] + 1;
					continue;
				}

				// look for a closing tag
				$close_tag_pos = self::get_close_tag ($content, $open_tag_pos ['end'] + 1, $open_tag_pos ['name']);
				if ($close_tag_pos !== null)
				{
					// everything up to the tag will have autobr applied
					// everything inside the tag will not have autobr applied
					if ($open_tag_pos ['start'] > $start_pos) // skip it if it is empty
//{
						$content_part [] = array ('start' => $start_pos, 'end' => $open_tag_pos ['start'] - 1, 'autobr' => true);
//$content_part [] = '[['.str_replace ('<', '&lt;', substr ($content, $start_pos, (($open_tag_pos ['start'] - 1) - $start_pos) + 1)).']]';
//}
					$content_part [] = array ('start' => $open_tag_pos ['start'], 'end' => $close_tag_pos ['end'], 'autobr' => false);
//$content_part [] = '[['.str_replace ('<', '&lt;', substr ($content, $open_tag_pos ['start'], ($close_tag_pos ['end'] - $open_tag_pos ['start']) + 1)).']]';
					$search_pos = $start_pos = $close_tag_pos ['end'] + 1;
				}
				else
				{
					// unclosed tag, skip it
//$content_part [] = 'A '.$open_tag_pos ['name'].' tag, at position '.$open_tag_pos ['start'].', was skipped because it is unclosed.';
					$search_pos = $open_tag_pos ['end'] + 1;
				}
			}
			else // no more tags were found
			{
				// the last (or only) part outside of any HTML tags
if ($start_pos < strlen ($content))
					$content_part [] = array ('start' => $start_pos, 'end' => null, 'autobr' => true);
//$content_part [] = '[['.substr ($content, $start_pos).']]';
				break;
			}
		}

		$new_content = '';
		foreach ($content_part as $cp)
		{
			if ($cp ['end'] === null)
				$part = substr ($content, $cp ['start']);
			else
				$part = substr ($content, $cp ['start'], ($cp ['end'] - $cp ['start']) + 1);

			if ($cp ['autobr'])
				$new_content .= str_replace ("\n", '<br />', $part);
			else
				$new_content .= $part;
		}

		return $new_content;
//die (str_replace ("\n", '<br />', print_r ($content_part, true)));
	}
}
?>
