<?php


namespace B2k\ZH2YT\Command\YouTrack;


use B2k\ZH2YT\YouTrack\YouTrackClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetIssueCommand extends Command
{
    protected YouTrackClient $client;

    public function __construct(YouTrackClient $client, string $name = 'yt:issue:get')
    {
        parent::__construct($name);
        $this->client = $client;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Get youtrack issue by id')
            ->addArgument('issue', InputArgument::REQUIRED, 'YouTrack Issue ID')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $issueId = $input->getArgument('issue');

        $result = $this->client->getIssue($issueId);
        $output->writeln(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return 0;
    }
}
