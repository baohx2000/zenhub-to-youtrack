<?php


namespace B2k\ZH2YT\Command\Migrate;


use B2k\ZH2YT\GitHub\GitHubClient;
use B2k\ZH2YT\YouTrack\YouTrackClient;
use B2k\ZH2YT\ZenHub\ZenHubClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MigrateEpicsCommand extends Command
{
    protected ZenHubClient $zenHubClient;
    protected YouTrackClient $youTrackClient;
    protected GitHubClient $gitHubClient;

    public function __construct(GitHubClient $gitHubClient, ZenHubClient $zenHubClient, YouTrackClient $youTrackClient, string $name = 'migrate')
    {
        parent::__construct($name);
        $this->zenHubClient = $zenHubClient;
        $this->youTrackClient = $youTrackClient;
        $this->gitHubClient = $gitHubClient;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Migrate epics - Note that this command does a dry run by default. Include --no-dry-run when ready to procede.')
            ->addArgument('project', InputArgument::REQUIRED, 'YouTrack Project ID')
            ->addArgument('repo-id', InputArgument::REQUIRED, 'GitHub REPO ID (numeric)')
            ->addOption('no-skip-closed', null, InputOption::VALUE_NONE, 'By default, we will not migrate closed epics')
            ->addOption('no-dry-run', null, InputOption::VALUE_NONE, 'Include this option when you wish to actually run the migration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        [
            'project' => $project,
            'repo-id' => $repoId,
        ] = $input->getArguments();

        /** @var QuestionHelper $asker */
        $asker = $this->getHelper('question');

        $skipClosed = !$input->getOption('no-skip-closed');
        $dryRun = !$input->getOption('no-dry-run');

        $epics = $this->zenHubClient->listEpics((int) $repoId);

        foreach ($epics as $epic) {
            $issueId = $epic->issue_number;

            preg_match('/([^\/]+)\/([^\/]+)\/issues\/(\d+)$/', $epic->issue_url, $matches);
            [, $org, $repo] = $matches;
            $ghEpic = $this->gitHubClient->getIssue($org, $repo, $issueId);
            ['title' => $title, 'body' => $body, 'labels' => $labels] = $ghEpic;

            if ($skipClosed && $ghEpic['state'] === 'closed') {
                if ($output->isVerbose()) {
                    $output->writeln("Skipping closed epic: {$title} ({$issueId})");
                }
                continue;
            }
            $zhEpic = $this->zenHubClient->getEpic($repoId, $issueId);

            $q = new ConfirmationQuestion(
                "Migrate issue {$issueId} {$title}?",
                false
            );

            if ($asker->ask($input, $output, $q)) {
                var_dump($ghEpic);
                //if ()
            }
            exit;
        }

        return 0;
    }
}
