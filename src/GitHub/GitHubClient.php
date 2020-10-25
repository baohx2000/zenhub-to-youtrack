<?php


namespace B2k\ZH2YT\GitHub;


use Github\Client;

class GitHubClient
{
    protected string $username;
    protected string $token;
    private Client $client;
    private bool $configured = false;

    public function __construct(string $username, string $token, Client $client)
    {
        $this->username = $username;
        $this->token = $token;
        $this->client = $client;
    }

    public function getClient(): Client
    {
        if (!$this->configured) {
            $this->client->authenticate($this->username, $this->token, Client::AUTH_HTTP_PASSWORD);

            $this->configured = true;
        }
        return $this->client;
    }

    public function getRepoId(string $repo): int
    {
        [$org, $repo] = explode('/', $repo, 2);
        $result = $this->getClient()
            ->repository()->show($org, $repo);

        return $result['id'];
    }

    public function issues(string $repo, string $filter): array
    {
        $query = "repo:{$repo} {$filter}";
        return $this->getClient()
            ->search()->issues($query)['items'];
    }

    public function getIssue($org, $repo, $issueId)
    {
        return $this->getClient()
            ->issues()->show($org, $repo, $issueId);
    }
}
