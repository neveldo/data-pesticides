# Intall the data-pesticide application ...

# Install PHP dependencies
composer install

# Uncompress source datasets
tar -C datasets/source/ -zxvf datasets/source.tar.gz

# Load datasets into the triplestore
bin/console data-pesticides:load-dataset --type=stations --file=datasets/source/stations.csv
bin/console data-pesticides:load-dataset --type=pesticides --file=datasets/source/pesticides.csv
bin/console data-pesticides:load-dataset --type=roles --file=datasets/source/fonctions.csv
bin/console data-pesticides:load-dataset --type=departments --file=datasets/source/depts2016.csv

bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2007.csv --year=2007
bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2008.csv --year=2008
bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2009.csv --year=2009
bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2010.csv --year=2010
bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2011.csv --year=2011
bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2012.csv --year=2012

bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2007.csv --year=2007
bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2008.csv --year=2008
bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2009.csv --year=2009
bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2010.csv --year=2010
bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2011.csv --year=2011
bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2012.csv --year=2012

# Warmup API cache

bin/console data-pesticides:warmup-api