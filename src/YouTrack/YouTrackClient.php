<?php


namespace B2k\ZH2YT\YouTrack;


class YouTrackClient
{
    protected \Cog\YouTrack\Rest\Client\YouTrackClient $client;

    public function __construct(\Cog\YouTrack\Rest\Client\YouTrackClient $client)
    {
        $this->client = $client;
    }

    public function getIssue(string $issueId): object
    {
        return json_decode(
            $this->client->get("/issue/{$issueId}")->body(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    public function findIssueByGithubId(string $projectId, int $issueId): array
    {
        $filter = urlencode("GitHub:{$issueId}");
        $response = $this->client->get(
            "/issue/byproject/{$projectId}?filter={$filter}"
        );

        return json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    public function getProjects(): array
    {
        return json_decode($this->client->get('/admin/project')->body(), false, 512, JSON_THROW_ON_ERROR);
    }
}
