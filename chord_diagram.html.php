<?php
if (!isset ($fingering))
	die;

$string_count = count ($fingering);

// determine the starting fret
$f = 0;
while ($f < $string_count && $fingering [$f] <= 0)
	++$f;
if ($f < $string_count)
{
	$bottom_fret = $top_fret = $fingering [$f++];
	while ($f < $string_count)
	{
		if ($fingering [$f] > 0 && $fingering [$f] < $top_fret)
			$top_fret = $fingering [$f];
		if ($fingering [$f] > $bottom_fret)
			$bottom_fret = $fingering [$f];
		++$f;
	}
}
else // they must all be either muted or open
{
	$top_fret = 1;
	$bottom_fret = 4;
}

if ($bottom_fret <= 4)
	$top_fret = 1;
if ($bottom_fret < $top_fret + 3)
	$bottom_fret = $top_fret + 3;
if ($bottom_fret > $top_fret + 4)
	$bottom_fret = $top_fret + 4;
$fret_count = ($bottom_fret - $top_fret) + 1;
$start_fret = $top_fret;

// calculate the diagram width
$width = (($string_count - 1) * 10) + 1;

// calculate the diagram height from the fret count
$diagram_height = $fret_count * 12;
$height = $diagram_height + 28; // title and open/muted strings

$vertical_align = ceil ($height / 2);

$horizontal_margin = $string_count >= 6 ? 8 : 18;
if (isset ($start_fret) && $start_fret > 1)
	$margin = ' margin:10px '.$horizontal_margin.'px 10px 1.5em; ';
else
	$margin = ' margin:10px '.$horizontal_margin.'px; ';
?>
<div style="display:inline-block; position:relative; height:<?php echo $height + 2; // top border ?>px; width:<?php echo $width; ?>px; vertical-align:<?php echo $vertical_align; ?>px; <?php echo $margin; ?>">
	<?php //if (isset ($chord_title)): ?>
		<div style="font-family:serif; height:14px; font-size:12px; width:<?php echo $width + 60; ?>px; margin-left:-30px; text-align:center; overflow:visible; color:black;">
			<?php echo !empty ($chord_title) ? $chord_title : '&nbsp;'; ?>
		</div>
	<?php //endif; ?>

<?php //if ($open_muted || isset ($chord_title)): ?>
	<div style="font-family:sans-serif; height:14px; font-size:10px; width:<?php echo $width + 10; ?>px; margin-left:-5px; color:black;">
		<?php // open and deadened strings ?>
		<?php for ($string = 0; $string < $string_count; $string++): ?>
			<div style="float:left; margin-left:1px; width:9px; font-family:DejaVuSansCondensed; color:black; overflow:hidden; text-align:center; line-height:14px; vertical-align:bottom;">
				<?php echo $fingering [$string] < 0 ? '⨯' : ($fingering [$string] == 0 ? '○' : '&nbsp;'); ?>
			</div>
		<?php endfor; ?>
		<div style="clear:both;"></div>
	</div>
<?php //endif; ?>

	<div style="position:relative; height:<?php echo $diagram_height - 1; // not including bottom border ?>px; border:1px solid black; border-top:2px solid black; width:<?php echo $width - 2; // outer borders ?>px;">
		<?php if (isset ($start_fret) && $start_fret > 1): ?>
			<div style="position:absolute; font-family:sans-serif; margin-top:0px; margin-left:-24px; text-align:right; font-size:10px; width:18px; height:11px; line-height:11px; vertical-align:middle; font-family:sans-serif; color:black; font-weight:bold;"><?php echo $start_fret; ?></div>
		<?php endif; ?>
		<?php // horizontal lines, frets
			$top = 11;
			for ($f = 1; $f < $fret_count; $f++):
		?>
				<div style="position:absolute; margin-top:<?php echo $top; ?>px; border-top:1px solid black; width:100%;"></div>
		<?php
			$top += 12;
			 endfor;
		?>

		<?php // vertical lines, strings
			$left = 9;
			for ($s = 1; $s < ($string_count - 1); $s++):
		?>
				<div style="position:absolute; margin-left:<?php echo $left; ?>px; border-left:1px solid black; height:<?php echo $diagram_height; ?>px;"></div>
		<?php
			$left += 10;
			 endfor;
		?>

		<?php // finger positions
			foreach ($fingering as $string => $fingering_fret):
		?>
			<?php if (!empty ($fingering_fret) && $fingering_fret >= 0 && ($fingering_fret - ($start_fret - 1)) <= $fret_count): ?>
				<div style="display:block; position:absolute; height:5px; width:5px; margin-top:<?php echo 2 + (12 * (($fingering_fret - ($start_fret - 1)) - 1)); ?>px; margin-left:<?php echo -4 + (10 * $string); ?>px; border:1px solid black; background-color:black; border-radius:3px 3px 3px 3px; -webkit-border-radius:3px 3px 3px 3px; -moz-border-radius:3px 3px 3px 3px;"></div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>

