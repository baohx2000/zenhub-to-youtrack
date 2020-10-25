<?php


namespace B2k\ZH2YT\ZenHub;


use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class ZenHubClient
{
    protected string $apiToken;
    private ?Client $client = null;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function getEpic($repoId, $issueId)
    {
        $result = $this->getClient()
            ->get("/p1/repositories/{$repoId}/epics/{$issueId}");

        return json_decode($result->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function getClient(): Client
    {
        if (!$this->client) {
            $handlerStack = HandlerStack::create();
            $handlerStack->push(Middleware::mapRequest(function (RequestInterface $request) {
                return $request
                    ->withHeader('X-Authentication-Token', $this->apiToken)
                    ;
            }));

            $this->client = new Client(['handler' => $handlerStack, 'base_uri' => 'https://api.zenhub.com']);
        }
        return $this->client;
    }

    public function listEpics(int $repoId)
    {
        $result = $this->getClient()
            ->get("/p1/repositories/{$repoId}/epics");

        return json_decode($result->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR)->epic_issues;
    }

    public function listIssues(int $repoId)
    {
        $result = $this->getClient()
            ->get("/p1/repositories/{$repoId}/epics");

        return json_decode($result->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
    }


}
