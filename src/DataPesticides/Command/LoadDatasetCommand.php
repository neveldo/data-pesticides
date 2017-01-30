<?php

namespace Neveldo\DataPesticides\Command;

use Neveldo\DataPesticides\Dataset\TriplesConverter\CityGeolocTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\CityTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\DepartmentTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\PesticideTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\RoleTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\StationStatementTotalTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\StationStatementTriplesConverter;
use Neveldo\DataPesticides\Dataset\TriplesConverter\StationTriplesConverter;
use Neveldo\DataPesticides\Dataset\Loader;
use Neveldo\DataPesticides\Dataset\Reader\CSVReader;
use Neveldo\DataPesticides\Dataset\Writer\TurtleWriter;
use Neveldo\DataPesticides\Triplestore\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class LoadDatasetCommand
 *
 * Allow to load a dataset (CSV) into a triplestore
 *
 * Execution : bin/console data-pesticides:load-dataset
 * Sample :
 * bin/console data-pesticides:load-dataset --type=stations --file=datasets/source/stations.csv
 * bin/console data-pesticides:load-dataset --type=pesticides --file=datasets/source/pesticides.csv
 * bin/console data-pesticides:load-dataset --type=roles --file=datasets/source/fonctions.csv
 * bin/console data-pesticides:load-dataset --type=departments --file=datasets/source/depts2016.csv
 *
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2007.csv --year=2007
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2008.csv --year=2008
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2009.csv --year=2009
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2010.csv --year=2010
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2011.csv --year=2011
 * bin/console data-pesticides:load-dataset --type=station_statements --file=datasets/source/analyses2012.csv --year=2012
 *
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2007.csv --year=2007
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2008.csv --year=2008
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2009.csv --year=2009
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2010.csv --year=2010
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2011.csv --year=2011
 * bin/console data-pesticides:load-dataset --type=station_statements_total --file=datasets/source/moy_tot_quantif_2012.csv --year=2012
 *
 * station_statements
 * @package Neveldo\DataPesticides\Command
 */
class LoadDatasetCommand extends Command
{
    /**
     * @var string Application root directory
     */
    private $appRootDir;

    /**
     * @var string sparql API endpoint
     */
    private $sparqlEndpointUrl;

    /**
     * @var array RDF namespaces
     */
    private $rdfNamepaces;

    /**
     * @var string graph prefix name
     */
    private $graphPrefix;

    /**
     * LoadDatasetCommand constructor.
     * @param string $appRootDir
     */
    public function __construct($appRootDir)
    {
        parent::__construct();

        $this->appRootDir = $appRootDir;

        // Read configuration file
        $config = require $appRootDir .'/app/config/config.php';

        $this->sparqlEndpointUrl = $config['sparql_endpoint_url'];
        $this->graphPrefix = $config['graph_prefix'];
        $this->rdfNamepaces = $config['rdf_namepaces'];
    }

    protected function configure()
    {
        $this
            ->setName('data-pesticides:load-dataset')
            ->setDescription('Load a dataset into the graph store')
            ->setHelp("This command allows you to load a dataset into the graph store.")
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'The dataset file path.')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The dataset type.')
            ->addOption('year', null, InputOption::VALUE_OPTIONAL, 'The dataset year.')
        ;
    }

    /**
     * Execute the command in order to load file
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting the command that will allow you to load new datasets into the graph database...</info>');

        $triplestoreClient = new Client($this->sparqlEndpointUrl);

        // Stores import configurations for each type of dataset that are likely to be loaded
        $configuration = [
            'stations' => [
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new StationTriplesConverter(),
                        $triplestoreClient
                    );
                }
            ],
            'pesticides' => [
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new PesticideTriplesConverter(),
                        $triplestoreClient
                    );
                }
            ],
            'roles' => [
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new RoleTriplesConverter(),
                        $triplestoreClient
                    );
                }
            ],
            'departments' => [
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new DepartmentTriplesConverter(),
                        $triplestoreClient
                    );
                }
            ],
            'station_statements' => [
                'require_year' => true,
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new StationStatementTriplesConverter($options['year']),
                        $triplestoreClient
                    );
                }
            ],
            'station_statements_total' => [
                'require_year' => true,
                'getLoader' => function($options) use ($triplestoreClient) {
                    return new Loader(
                        new CSVReader($options['file']),
                        $this->buildTurtleWriter($options),
                        new StationStatementTotalTriplesConverter($options['year']),
                        $triplestoreClient
                    );
                }
            ],
        ];

        $options = $this->readInputOptions($input, $output, $configuration);
        $output->writeln('<info>Starting import of file ' . $options['file'] . ' ...</info>');

        $report = $configuration[$options['type']]['getLoader']($options)->load($this->graphPrefix);

        if (isset($report['error'])) {
            $output->writeln(sprintf('<error>Error from the triplestore client : "%s"</error>', $report['error']));
            return;
        }

        // Print load report to the user
        $output->writeln('<info>Import ended.</info>');
        $output->writeln(sprintf(
            '<info>%s triples written into the file "%s"</info>',
            $report['rdf_triples'],
            $report['destination_file']
        ));
        $output->writeln(sprintf(
            '<info>%s triples have been deleted from graph "%s"</info>',
            $report['deleted_triples'],
            $report['destination_graph']
        ));
        $output->writeln(sprintf(
            '<info>%s triples have been inserted into graph "%s"</info>',
            $report['inserted_triples'],
            $report['destination_graph']
        ));
    }

    /**
     * @param array $options
     * Configure and return a TurtleWriter intance depending on the array of options
     * @return TurtleWriter
     */
    protected function buildTurtleWriter(array $options)
    {
        return (new TurtleWriter($this->appRootDir . '/datasets/rdf/' . pathinfo($options['file'])['filename'] . '.ttl'))
            ->registerNamespaces($this->rdfNamepaces);
    }

    /**
     * Read user options (filepath, filetype and year) from the command arguments or by asking for them
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $types
     * @return array
     */
    protected function readInputOptions(InputInterface $input, OutputInterface $output, array $types)
    {
        $type = $input->getOption('type');
        if ($type === null) {
            $question = new Question(
                sprintf(
                    'Please enter the type the dataset you want to import. Available types are : %s : ',
                    implode(', ', array_keys($types))
                )
            );

            $type = $this->getHelper('question')->ask($input, $output, $question);
        }

        if (!isset($types[$type])) {
            $output->writeln(
                sprintf('<error>Unavailable type "%s". Available types are : %s.</error>',
                    $type,
                    implode(', ', array_keys($types))
                ));
            exit;
        }

        $file = $input->getOption('file');
        if ($file === null) {
            $question = new Question('Please enter the filepath of the dataset : ');
            $file = $this->getHelper('question')->ask($input, $output, $question);
        }
        if (!file_exists($file)) {
            $output->writeln(sprintf('<error>File "%s" not found.</error>', $file));
            exit;
        }

        $year = $input->getOption('year');

        if (isset($types[$type]['require_year']) && $types[$type]['require_year'] === true) {
            if ($year === null) {
                $question = new Question('Please enter the year of the dataset : ');
                $year = $this->getHelper('question')->ask($input, $output, $question);
            }

            if (!preg_match("/^\d{4}$/", $year, $matches)) {
                $output->writeln(sprintf('<error>"%s" is not a valid year.</error>', $year));
                exit;
            }
        }

        return [
            'type' => $type,
            'file' => $file,
            'year' => $year,
        ];
    }
}