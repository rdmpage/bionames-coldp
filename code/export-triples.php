<?php

// Take BioNames dump and export triples for some subset of the data

error_reporting(E_ALL);


$filename = 'bionames.tsv';

$headings = array();

$row_count = 0;

$file = @fopen($filename, "r") or die("couldn't open $filename");
		
$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		"\t" 
		);
		
	$go = is_array($row);
	
	if ($go)
	{
		if ($row_count == 0)
		{
			$headings = $row;
		}
		else
		{
			$obj = new stdclass;
		
			foreach ($row as $k => $v)
			{
				if ($v != '')
				{
					if (isset($headings[$k]))
					{
						$obj->{$headings[$k]} = $v;
					}
				}
			}
			
			// print_r($obj);
			
			$go = false;
			
			if (isset($obj->doi))
			{
				$go = true;
			}

			if (!isset($obj->nameComplete))
			{
				$go = false;
			}
			
			if ($go)
			{
			
				$triples = array();

				// TaxonName		
				$triple = array();
				$triple[] = '<urn:lsid:organismnames.com:name:' . $obj->id . '>';
				$triple[] = '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>';
				$triple[] = '<http://schema.org/TaxonName>';

				$triples[] = $triple;

				$triple = array();
				$triple[] = '<urn:lsid:organismnames.com:name:' . $obj->id . '>';
				$triple[] = '<http://schema.org/name>';
				$triple[] = '"' . str_replace('"', '\"', $obj->nameComplete) . '"';

				$triples[] = $triple;

				if (isset($obj->doi))
				{
					$doi = $obj->doi;
					$doi = str_replace('<', '%3C', $doi);
					$doi = str_replace('>', '%3E', $doi);

					$doi = str_replace('[', '%5B', $doi);
					$doi = str_replace(']', '%5D', $doi);
	
					$doi = strtolower($doi);

					$triple = array();
					$triple[] = '<urn:lsid:organismnames.com:name:' . $obj->id . '>';
					$triple[] = '<http://schema.org/isBasedOn>';
					$triple[] = '<https://doi.org/' . $doi . '>';
	
					$triples[] = $triple;
	
					if (isset($obj->title))
					{
						$triple = array();
						$triple[] = '<https://doi.org/' . $doi . '>';
						$triple[] = '<http://schema.org/name>';
						$triple[] = '"' . str_replace('"', '\"', $obj->title) . '"';
	
						$triples[] = $triple;			
					}
				}
				
				// print_r($triples);
				
				foreach ($triples as $triple)
				{
					echo join(" ", $triple) . " . \n";
				}
				
			
			}
		}
	}	
	$row_count++;
	
	
	if ($row_count > 1000000)
	{
		exit();
	}
	
	
}
?>
