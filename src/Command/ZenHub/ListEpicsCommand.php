<?php


namespace B2k\ZH2YT\Command\ZenHub;


use B2k\ZH2YT\ZenHub\ZenHubClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListEpicsCommand extends Command
{
    protected ZenHubClient $client;

    public function __construct(ZenHubClient $client, string $name = 'zh:epic:list')
    {
        parent::__construct($name);
        $this->client = $client;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('List epics in ZenHub')
            ->addArgument('repo', InputArgument::REQUIRED, 'Repo ID (numeric - use gh:repoid to get the id by org/name)')
            ->addOption('label', 'l', InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Label to filter by')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->client->listEpics($input->getArgument('repo'));

        $output->writeln(json_encode($result, JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
        return 0;
    }
}
