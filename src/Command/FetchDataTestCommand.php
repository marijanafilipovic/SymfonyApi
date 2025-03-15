<?php

namespace App\Command;

use App\Service\DataTransformerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'FetchDataTestCommand',
    description: 'Add a short description for your command',
)]
class FetchDataTestCommand extends Command
{
    private DataTransformerService $dataTransformerService;

    public function __construct(DataTransformerService $dataTransformerService)
    {
        parent::__construct();
        $this->dataTransformerService = $dataTransformerService;

    }
  protected static $defaultName = 'app:fetch-data-test';

    protected function configure()
    {
        $this->setDescription('Fetch data and test transformation.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Simulate fetching data from the external API
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://external-api.com/endpoint');

        // Assuming the API response is JSON
        $rawData = $response->toArray();

        // Call your service to transform and store the data
        $this->dataTransformerService->transformAndStore($rawData);

        $output->writeln('Data fetched and stored successfully.');

        return Command::SUCCESS;
    }
}
