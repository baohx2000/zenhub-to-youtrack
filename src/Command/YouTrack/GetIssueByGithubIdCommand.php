<?php


namespace B2k\ZH2YT\Command\YouTrack;


use B2k\ZH2YT\YouTrack\YouTrackClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetIssueByGithubIdCommand extends Command
{
    protected YouTrackClient $client;

    public function __construct(YouTrackClient $client, string $name = 'yt:issue:github')
    {
        parent::__construct($name);
        $this->client = $client;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Find youtrack issue(s) already linked to a github issue')
            ->addArgument('project', InputArgument::REQUIRED, 'YouTrack Project Name')
            ->addArgument('issue', InputArgument::REQUIRED, 'GitHub Issue ID')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $input->getArgument('project');
        $issueId = (int) $input->getArgument('issue');

        $result = $this->client->findIssueByGithubId($project, $issueId);
        $output->writeln(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return 0;
    }
}
