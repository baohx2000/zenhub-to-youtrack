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

class MigrateByTagCommand extends Command
{
    protected ZenHubClient $zenHubClient;
    protected YouTrackClient $youTrackClient;
    protected GitHubClient $gitHubClient;

    public function __construct(GitHubClient $gitHubClient, ZenHubClient $zenHubClient, YouTrackClient $youTrackClient, string $name = 'migrate:by-tag')
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
            ->setDescription('Migrate issues filtered by tags - Note that this command does a dry run by default. Include --no-dry-run when ready to procede.')
            ->addArgument('project', InputArgument::REQUIRED, 'YouTrack Project ID')
            ->addArgument('repo', InputArgument::REQUIRED, 'GitHub repo name (github/fetch)')
            ->addArgument('tag', InputArgument::REQUIRED, 'tag to filter by')
            ->addArgument('ytIssueType', InputArgument::REQUIRED, 'Issue type to create in YouTrack')
            ->addOption('no-dry-run', null, InputOption::VALUE_NONE, 'Include this option when you wish to actually run the migration')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        [
            'project' => $project,
            'repo' => $repo,
            'tag' => $tag,
            'ytIssueType' => $ytIssueType,
        ] = $input->getArguments();


        $dryRun = !$input->getOption('no-dry-run');

        $issues = $this->gitHubClient->issues($repo, "label:\"{$tag}\" state:open");

        foreach ($issues as $ghIssue) {
            if (!$dryRun) {
                $result = $this->youTrackClient->findIssueByGithubId($project, $ghIssue['number']);
                if ($result) {
                    $output->writeln("<warning>Issue {$ghIssue['number']} already exists in youtrack</warning>");
                    continue;
                }

                $labels = array_map(function ($l) {
                    return $l['name'];
                }, $ghIssue['labels']);
                if (in_array("Feature Request ✨", $labels, true)) {
                    $type = 'Feature';
                }

                try {
                    $ytIssueId = $this->youTrackClient->makeItem(
                        $project,
                        $ytIssueType,
                        $ghIssue['title'],
                        "Migrated from https://github.com/pagely/mgmt/issues/{$ghIssue['number']}\n".
                        $ghIssue['body'],
                        null,
                        $labels,
                        $ghIssue['number']
                    );
                    if ($output->isVerbose()) {
                        $output->writeln("  <info>Created {$ytIssueType} {$ytIssueId}</info>");
                    }
                } catch (\Throwable $e) {
                    $output->writeln("<error>Unable to migrate GitHub issue {$ghIssue['number']}</error>");
                }
            }
        }

        return 0;
    }
}
