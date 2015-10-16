<?php
defined('_JEXEC') or die;

// Output filter
jimport('joomla.filter.filteroutput');

// Feed title
echo '<a href="http://feeds.feedburner.com/JoomlaLinkr" target="_blank">Linkr RSS</a>';

// Feed items
echo '<div style="padding:4px;">';
$items	= $this->feed->get_items();
$total	= count($items);
for ($n = 0; $n < $total, $n < 3; $n++)
{
	echo '<div>';
	$i	= & $items[$n];
	
	// Item link
	$link	= $i->get_link();
	$link	= $link ? $link : 'http://j.l33p.com/linkr';
	$link	= JHTML::link($link, $i->get_title(), array('target' => '_blank'));
	
	// Title
	echo '<strong>'. $link .'</strong>';
	
	// Description
	$desc	= preg_replace('/<a\s+.*?href="[^"]+"[^>]*>([^<]+)<\/a>/is', '\1', $i->get_description());
	$desc	= JFilterOutput::cleanText($desc);
	//$desc	= html_entity_decode($desc, ENT_COMPAT, 'UTF-8');
	$desc	= html_entity_decode($desc, ENT_COMPAT); // PHP4 compatibility
	if (strlen($desc) > 140) {
		$desc	= substr($desc, 0, 140) .'...';
	}
	echo ': '. htmlspecialchars($desc, ENT_COMPAT, 'UTF-8');
	
	echo '</div>';
}
echo '</div>';

