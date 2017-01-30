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
 * Class WarmupApiCommand
 * Allows to warm-up the data-visualisation API
 *
 * Sample :
 * bin/console data-pesticides:warmup-api
 *
 * @package Neveldo\DataPesticides\Command
 */
class WarmupApiCommand extends Command
{
    private $urlsToWarmup = [
        '/api/data/getYears',
        '/api/data/getFamilies',
        '/api/data/getStations',
        '/api/data/getDepartments',
        '/api/data/getCountryData',
        '/api/data/getTotalConcentrationsByStation',
        '/api/data/getTotalConcentrationsByDepartment',
        '/api/data/getConcentrationsByFamilyAndStation',
        '/api/data/getConcentrationsByFamilyAndDepartment',
        '/api/data/getPesticidesInExcessByStation',
    ];

    private $host;

    /**
     * WarmupApiCommand constructor.
     */
    public function __construct($appRootDir)
    {
        parent::__construct();

        $this->appRootDir = $appRootDir;

        // Read configuration file
        $config = require $appRootDir .'/app/config/config.php';

        $this->host = $config['host'];
    }

    protected function configure()
    {
        $this
            ->setName('data-pesticides:warmup-api')
            ->setDescription('Warm-up the data-visualisaion API')
            ->setHelp("This command allows you warm-up the data-visualisaion API.")
        ;
    }

    /**
     * Execute the command in order to load file
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Warmup API URLs...</info>');

        foreach($this->urlsToWarmup as $url) {
            $url = $this->host . $url . '?refresh';

            $output->writeln('<info>Warmup ' . $url . '...</info>');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $output->writeln('<info>HTP Result code : ' . $httpcode . '</info>');

            sleep(10);
        }
    }
}