<?php


namespace B2k\ZH2YT\Command\GitHub;


use B2k\ZH2YT\GitHub\GitHubClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IssuesCommand extends Command
{
    protected GitHubClient $githubClient;

    public function __construct(GitHubClient $githubClient, string $name = 'gh:issue:search')
    {
        parent::__construct($name);
        $this->githubClient = $githubClient;
    }

    public function configure(): void
    {
        parent::configure();
        $this->addArgument('repo', InputArgument::REQUIRED, 'Github repo name "org/repo"')
            ->addArgument('query', InputArgument::OPTIONAL, 'Additional query')
            ->addOption('label', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption('state', 's', InputOption::VALUE_REQUIRED, 'Issue state (open/closed)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $q = (string) $input->getArgument('query');
        $q .= (implode(
            ' ',
            array_map(
                function ($l) {
                    return "label:{$l}";
                },
                $input->getOption('label')
            )
        ));
        if ($state = $input->getOption('state')) {
            $q .= " state:{$state}";
        }

        $result = $this->githubClient
            ->issues($input->getArgument('repo'), $q);

        $output->writeln(json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return 0;
    }
}
