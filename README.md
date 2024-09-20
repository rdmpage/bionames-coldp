#  BioNames as a Catalogue of Life Data Package (ColDP)

[![DOI](https://zenodo.org/badge/528528140.svg)](https://zenodo.org/badge/latestdoi/528528140)

## Notes

Input data is a TSV dump of the table `ion.names`. We parse that and extract rows that are relevant to either ChecklistBank or RDF.

For ChecklistBank we extract names and references (depending on the setting of `$mode` in `parse.php`) and output TSV files that can be uploaded to ChecklistBank.

```
SELECT 
IFNULL(id,'') AS id,
IFNULL(cluster_id,'') AS cluster_id,
IFNULL(`group`,'') AS `group`,
IFNULL(nameComplete,'') AS nameComplete,
IFNULL(taxonAuthor,'') AS taxonAuthor,
IFNULL(uninomial,'') AS uninomial,
IFNULL(genusPart,'') AS genusPart,
IFNULL(infragenericEpithet,'') AS infragenericEpithet,
IFNULL(rank,'') AS rank,
IFNULL(publication,'') AS publication,
IFNULL(year,'') AS year,

IFNULL(microreference,'') AS microreference,


IFNULL(title,'') AS title,
IFNULL(journal,'') AS journal,
IFNULL(issn,'') AS issn,
IFNULL(volume,'') AS volume,
IFNULL(issue,'') AS issue,
IFNULL(spage,'') AS spage,
IFNULL(epage,'') AS epage,

IFNULL(isbn,'') AS isbn,

IFNULL(doi,'') AS doi,
IFNULL(sici,'') AS sici,
IFNULL(wikidata,'') AS wikidata
FROM names 
WHERE  publication IS NOT NULL;
```

### LFS

Note that the `.tsv` files may need LFS to be committed, which in turn means we need to adjust a setting to include them in the archive software a release, see [About Git LFS objects in archives](https://docs.github.com/en/repositories/managing-your-repositorys-settings-and-features/managing-repository-settings/managing-git-lfs-objects-in-archives-of-your-repository).


### Triple store

Create triples using `export-triples.php` which generates triples creating taxon name and linking to publication. Can upload to local Oxigraph for testing and exploration. 

```
curl 'http://localhost:7878/store?graph=http://www.organismnames.com' -H 'Content-Type:application/n-triples' --data-binary '@triples.nt'  --progress-bar
```

### DOI names dump

To create a simple dump of names that are linked to DOIs run `names_doi.php > ion-unsorted.tsv` in `/code`. This will write a header file separately, then dump the data. To sort the dumped names:

```
sort ion-unsorted.tsv > ion.tsv
```

```
cat header.tsv ion.tsv > ion.tsv 
```

### Versioning

Between releases we can use [csvdiff](https://github.com/aswinkarthik/csvdiff) to compare TSV files and publish those diff files so that users can see what has changed.

For example,

```
csvdiff references-old.tsv references.tsv --lazyquotes -s "\t" > references.diff
```

```
csvdiff names-old.tsv names.tsv -s "\t" > names.diff
```
