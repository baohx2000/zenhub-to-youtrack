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

    public function makeEpic(string $project, string $title, string $body, int $epicId)
    {
        $response = $this->client->put(
            'issue',
            [],
            [
                'query' => [
                    'project' => $project,
                    'summary' => $title,
                    'description' => $body,
                    'Github Issue' => $epicId,
                    'type' => 'Epic',
                ]
            ]
        );

        $url = explode('/', $response->location());

        $id = end($url);

        $command = "Github Issue {$epicId}";
        $this->client->post("issue/{$id}/execute", [], [
            'form_params' => [
                'command' => $command,
                'disableNotifications' => 'true',
            ],
        ]);

        return $id;
    }

    public function makeItem(
        string $project,
        string $type,
        string $title,
        string $body,
        string $parentEpic,
        array $labels,
        int $ghId
    ) {
        $response = $this->client->put(
            'issue',
            [],
            [
                'query' => [
                    'project' => $project,
                    'summary' => $title,
                    'description' => $body,
                    'type' => $type,
                ]
            ]
        );

        $url = explode('/', $response->location());
        $id = end($url);

        $command = "add subtask of {$parentEpic} Github Issue {$ghId}";
        foreach ($labels as $label) {
            $command .= " tag {$label}";
        }
        $this->client->post("issue/{$id}/execute", [], [
            'form_params' => [
                'command' => $command,
                'disableNotifications' => 'true',
            ],
        ]);

        return $id;
    }

    public function removeTag(string $tag)
    {
        $response = $this->client->delete("user/tag/{$tag}");
        return $response->isStatusCode(200);
    }
}
