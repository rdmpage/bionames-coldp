<?php

// Get Wikidata coverage for publications in a database, use this to proritise adding
// publications to Wikidata

error_reporting(E_ALL ^ E_DEPRECATED);

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' , 'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



//----------------------------------------------------------------------------------------

?>
<html>
  <head>
  	<title>Wikidata bibliographic coverage</title>
    <style type="text/css">
	
	body {
        padding: 1em;
        margin: 1em;
        font-family:sans-serif;
    }
    
    td {
    	font-family:sans-serif;
    	padding:4px;
    }
    
    .doi {
    	background-color:#9FC;
    }
    .jstor {
    	background-color:#F93;
    }
    .handle {
    	background-color:#CFF;
    }
    .url {
    	background-color:#9FF;
    } 
    .pdf {
    	background-color:#F60;
    }            
    .biostor {
    	background-color:#FCC;
    } 
    .cinii {
    	background-color:#9C3;
    } 
    .isbn {
    	background-color:#FF9;
    } 

    
    </style>
  </head>
  <body>
  <h1>Wikidata coverage</h1>
  <p>The different identifier types for publications and the number of records
  which have an identifier but no corresponding Wikidata entry.</p>
  
<?php

$identifiers = array('doi', 'jstor', 'handle', 'url', 'pdf', 'biostor', 'cinii', 'isbn');

echo '<h2>BioNames</h2>';

echo '<ul>';
foreach ($identifiers as $identifier)
{
	echo '<li><a href="#' . $identifier . '">' . $identifier . '</li>';		
}	

echo '</ul>';
	
echo '<div>';

foreach ($identifiers as $identifier)
{
	$sql = 'SELECT COUNT(id) AS count, journal AS container FROM names WHERE <IDENTIFIER> IS NOT NULL AND journal IS NOT NULL AND wikidata IS NULL GROUP BY journal ORDER BY count DESC';
	
	$sql = str_replace('<IDENTIFIER>', $identifier, $sql);
		
	$data = array();
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{	
		$d = new stdclass;
		$d->count = $result->fields['count'];
		$d->container = $result->fields['container'];
		
		$data[] = $d;
	
		$result->MoveNext();
	}	


	echo '<h2>' . '<a name="' . $identifier . '">' . strtoupper($identifier) . '</h2>';


	echo '<table style="width:70%" cellspacing="0" cellpadding="0">';
	echo '<tbody class="' . $identifier . '">';
	echo "\n";
	foreach ($data as $d)
	{
		echo '<tr>';

		echo '<td>' . $d->count . '</td>';
		echo '<td>' . $d->container . '</td>';

		echo '</tr>';

		echo "\n";
	}
	echo '</tbody>';
	echo '</table>';
	echo "\n";
}

echo '</div>';


?>


</body>
</html>
