<?php


namespace B2k\ZH2YT\Command\YouTrack;


use B2k\ZH2YT\YouTrack\YouTrackClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListProjectsCommand extends Command
{
    protected YouTrackClient $client;

    public function __construct(YouTrackClient $client, string $name = 'yt:project:list')
    {
        parent::__construct($name);
        $this->client = $client;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Get youtrack projects')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->client->getProjects();
        $output->writeln(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return 0;
    }
}
