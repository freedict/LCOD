# You have to be logged in as postgres user (sudo -u postgres make installExtensions)
installExtensions:
	psql -d freedict -f createExtensions.sql


teis2sqls:
	python3 -c 'import scripts; scripts.teis2sqls()'


# Environment variable PGPASSWORD has to be set e.g. (PGPASSWORD=foopwd make teis2sqls)
importSqls:
	python3 -c 'import scripts; scripts.importSqls()'


