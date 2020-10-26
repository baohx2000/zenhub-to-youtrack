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
            ->addOption('skip', null, InputOption::VALUE_REQUIRED, 'Skip this number of epics initially')
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
        $skip = (int) $input->getOption('skip');

        foreach ($epics as $key => $epic) {
            if ($key < $skip) {
                continue;
            }
            $epicId = $epic->issue_number;

            preg_match('/([^\/]+)\/([^\/]+)\/issues\/(\d+)$/', $epic->issue_url, $matches);
            [, $org, $repo] = $matches;
            $ghEpic = $this->gitHubClient->getIssue($org, $repo, $epicId);
            ['title' => $title, 'body' => $body /*, 'labels' => $labels */] = $ghEpic;

            if ($skipClosed && $ghEpic['state'] === 'closed') {
                if ($output->isVerbose()) {
                    $output->writeln("Skipping closed epic: {$title} ({$epicId})");
                }
                continue;
            }

            $q = new ConfirmationQuestion(
                "Migrate epic {$epicId} {$title}?",
                false
            );

            if ($output->isQuiet() || $asker->ask($input, $output, $q)) {
                $zhEpic = $this->zenHubClient->getEpic($repoId, $epicId);
                //var_dump($zhEpic);
                if (!$dryRun) {
                    $ytEpicId = $this->youTrackClient->makeEpic(
                        $project,
                        $title,
                        $body,
                        $epicId
                    );
                    if ($output->isVerbose()) {
                        $output->writeln("<info>Created Epic {$ytEpicId}</info>");
                    }
                }

                foreach ($zhEpic['issues'] as $task) {
                    $type = $task['is_epic'] ? 'User Story' : 'Task';
                    $ghIssue = $this->gitHubClient->getIssue($org, $repo, $task['issue_number']);
                    if ($ghIssue['state'] === 'closed') {
                        // only migrating open issues
                        continue;
                    }

                    if (!$dryRun) {
                        $labels = array_map(function ($l) {
                            return $l['name'];
                        }, $ghIssue['labels']);
                        $ytIssueId = $this->youTrackClient->makeItem(
                            $project,
                            $type,
                            $ghIssue['title'],
                            $ghIssue['body'],
                            $ytEpicId,
                            $labels,
                            $ghIssue['number']
                        );
                        if ($output->isVerbose()) {
                            $output->writeln("<info>Created {$type} {$ytIssueId}</info>");
                        }
                        exit;
                    }
                }
            }
            exit;
        }

        return 0;
    }
}
