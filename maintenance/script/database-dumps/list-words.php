#!/usr/bin/env php
<?php

/*
 * Return raw list of words which are contained in the database,
 * in Samoan alphabetical order (if the sortkey is correct, that is!).
 */
namespace SmWeb;

require_once (dirname ( __FILE__ ) . "/../../../api/core.php");
core::loadClass ( "database" );

$query = "SELECT spelling_t_style FROM {TABLE}spelling` WHERE 1 ORDER BY spelling_sortkey_sm;";
$res = database::retrieve ( $query, 0 );
while ( $row = database::get_row ( $res ) ) {
	echo $row [0] . "\n";
}
