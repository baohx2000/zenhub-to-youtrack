<?php


namespace B2k\ZH2YT\Command\YouTrack;


use B2k\ZH2YT\YouTrack\YouTrackClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteTagCommand extends Command
{
    protected YouTrackClient $client;

    public function __construct(YouTrackClient $client, string $name = 'yt:tag:remove')
    {
        parent::__construct($name);
        $this->client = $client;
    }

    public function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Remove a tag from the entire system')
            ->addArgument('tag', InputArgument::REQUIRED, 'Tag value')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $tag = $input->getArgument('tag');
        $result = $this->client->removeTag($tag);
        if ($result) {
            $output->writeln("Tag {$tag} removed.");
        }

        return 0;
    }
}
