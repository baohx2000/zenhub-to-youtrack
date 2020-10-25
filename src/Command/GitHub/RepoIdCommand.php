<?php


namespace B2k\ZH2YT\Command\GitHub;


use B2k\ZH2YT\GitHub\GitHubClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepoIdCommand extends Command
{
    protected GitHubClient $githubClient;

    public function __construct(GitHubClient $githubClient, string $name = 'gh:repo:id')
    {
        parent::__construct($name);
        $this->githubClient = $githubClient;
    }

    public function configure(): void
    {
        parent::configure();
        $this->addArgument('name', InputArgument::REQUIRED, 'Github repo name "org/repo"');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->githubClient->getRepoId($input->getArgument('name')));

        return 0;
    }
}
