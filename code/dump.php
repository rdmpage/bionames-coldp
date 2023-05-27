<?php

// For decade-clutsered journals add counts of identifiers
error_reporting(E_ALL ^ E_DEPRECATED);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' , 'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$keys = array('id','cluster_id','group','nameComplete','taxonAuthor','uninomial','genusPart','infragenericEpithet','rank','publication','year','microreference','title','journal','issn','volume','issue','spage','epage','isPartOf', 'isbn','doi','sici','wikidata');

echo join("\t", $keys) . "\n";

$page = 1000;
$offset = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM names WHERE doi IS NOT NULL OR wikidata IS NOT NULL';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$row = array();
		
		foreach ($keys as $k)
		{
			if ($result->fields[$k] != '')
			{
				$row[] = $result->fields[$k];
			}
			else
			{
				$row[] = '';
			}
		}
		
		echo join("\t", $row) . "\n";


		$result->MoveNext();

	}
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		// If we want to bale out and check it worked
		//if ($offset > 1000) { $done = true; }
	}
	

}

?>
