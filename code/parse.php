<?php

error_reporting(E_ALL);


//----------------------------------------------------------------------------------------



$name_key_mapping = array
(
'id' 					=> 'ID',
'nameComplete' 			=> 'scientificName',
'taxonAuthor' 			=> 'authorship',
'uninomial'				=> 'uninomial',
'genusPart'				=> 'genus',
'infragenericEpithet' 	=> 'infragenericEpithet',
'specificEpithet' 		=> 'specificEpithet',
'infraspecificEpithet' 	=> 'infraspecificEpithet',

'rank'					=> 'rank',

'year'					=> 'publishedInYear',
'microreference'		=> 'publishedInPage',
'sici'					=> 'referenceID',

);

$name_headings = array_values($name_key_mapping);
$name_headings[] = 'code';
$name_headings[] = 'link';


$sicis = array();


$reference_key_mapping = array
(
'sici'			=> 'ID',

'type'			=> 'type',

'publication' 	=> 'citation',
'title'			=> 'title',
'journal'		=> 'containerTitle',
'issn'			=> 'issn',
'volume'		=> 'volume',
'issue'			=> 'issue',
'spage'			=> 'page',
'year'			=> 'issued',

'publisher'		=> 'publisher',
'publisherPlace' => 'publisherPlace',
'isbn'			=> 'isbn',


'doi'			=> 'doi',


);

$reference_headings = array_values($reference_key_mapping);
$reference_headings[] = 'link';



$mode = 0; // 1 = references, 0 = taxa

switch ($mode)
{
	case 1:
		echo join("\t", $reference_headings) . "\n";
		break;
		
	case 0:
	default:
		echo join("\t", $name_headings) . "\n";
		break;
}


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
			
			if ($mode == 0)
			{
				if (isset($obj->sici))
				{		
					// print_r($obj);
				
					$export = new stdclass;
					$export->code = 'ICZN';
				
					foreach ($obj as $k => $v)
					{
						switch ($k)
						{
							case 'id':
								$export->{$name_key_mapping[$k]} = 'urn:lsid:organismnames.com:name:' . $v;							
								$export->link = 'https://bionames.org/' . $export->{$name_key_mapping[$k]};							
								break;
					
							default:
								if (isset($name_key_mapping[$k]))
								{
									$export->{$name_key_mapping[$k]} = $v;
								}
								break;
						}
				
					}
				
					//print_r($export);
					
					$row = array();
				
					foreach ($name_headings as $heading)
					{
						if (isset($export->{$heading}))
						{
							$row[] = $export->{$heading};
						}
						else
						{
							$row[] = '';
						}
				
					}
				
					//print_r($row);
					
					echo join("\t", $row) . "\n";
				
				}
			}
			
			
			
			if ($mode == 1)
			{
				if (isset($obj->sici))
				{		
					// print_r($obj);
				
					$export = new stdclass;
					
					$export->type = 'article-journal';
				
					foreach ($obj as $k => $v)
					{
						switch ($k)
						{
							case 'sici':
								$export->{$reference_key_mapping[$k]} = $v;							
								$export->link = 'https://bionames.org/references/' . $v;							
								break;
								
							case 'title':
								// BioNames may have embedded information in the title
								$pos = strpos($v, '{"publisher"');
								if ($pos === false)
								{
								}
								else
								{
									$p = substr($v, $pos);
									$v = substr($v, 0, $pos);
				
									$po = json_decode($p);
									if ($po)
									{
										if ($obj->isPartOf == 'Y')
										{
											$export->type = "chapter";
										}
										else
										{				
											$export->type = 'book';
										}
										
										if (isset($po->publisher->name))
										{
											$export->publisher = $po->publisher->name;
										}
										if (isset($po->publisher->address))
										{
											$export->publisherPlace = $po->publisher->address;
										}
									}
								}
								$export->{$reference_key_mapping[$k]} = $v;
								break;
								
							case 'publication':
								$v = preg_replace('/\s+(\d+|[ixvcl]+)\s+\[Zoological Record(.*)$/', '', $v);
								$export->{$reference_key_mapping[$k]} = $v;
								break;
								
							case 'spage':
								$export->{$reference_key_mapping[$k]} = $v;
								
								if (isset($obj->epage))
								{
									$export->{$reference_key_mapping[$k]} .= '-' . $obj->epage;
								}
								break;					
					
							default:
								if (isset($reference_key_mapping[$k]))
								{
									$export->{$reference_key_mapping[$k]} = $v;
								}
								break;
						}
				
					}
					
					// ensure unique
					
					if (!in_array($obj->sici, $sicis))
					{
						$sicis[] = $obj->sici;						
				
						// print_r($export);
					
						$output_row = array();
				
						foreach ($reference_headings as $heading)
						{
							if (isset($export->{$heading}))
							{
								$output_row[] = $export->{$heading};
							}
							else
							{
								$output_row[] = '';
							}
				
						}
				
						//print_r($output_row);
					
						echo join("\t", $output_row) . "\n";
						
						/*
						if ($export->type == 'book')
						{
							exit();
						}
						*/
					}				
				}
			}
		}
	}	
	$row_count++;
	
	/*
	if ($row_count > 100000)
	{
		exit();
	}
	*/
	
}
?>
