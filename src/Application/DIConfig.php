<?php


namespace B2k\ZH2YT\Application;


use Auryn\Injector;
use B2k\ZH2YT\GitHub\GitHubClient;
use B2k\ZH2YT\ZenHub\ZenHubClient;
use Cog\YouTrack\Rest\Authorizer\TokenAuthorizer;
use Cog\YouTrack\Rest\Client\YouTrackClient;
use Cog\YouTrack\Rest\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Dotenv\Dotenv;

class DIConfig
{
    public function __invoke(Injector $injector)
    {
        $appPath = dirname(__DIR__, 2);
        $dotenv = new Dotenv();
        if (file_exists($appPath . '/.env')) {
            $dotenv->load($appPath . '/.env');
        }
        $homeConfig = getenv('HOME') . '/.zh2yt.env';
        if (file_exists($homeConfig)) {
            $dotenv->load($homeConfig);
        }

        $zhToken = $_ENV['ZH_TOKEN'];
        $ytUser = $_ENV['YT_USER'];
        $ytToken = $_ENV['YT_TOKEN'];
        $ytDomain = $_ENV['YT_DOMAIN'];
        $ghUser = $_ENV['GH_USER'];
        $ghToken = $_ENV['GH_TOKEN'];
        if (!$zhToken) {
            throw new \RuntimeException('ZH_TOKEN is not defined. Please see README');
        }
        if (!$ytUser || !$ytToken) {
            throw new \RuntimeException('YT_USER or YT_TOKEN is not defined. Please see README');
        }
        if (!$ghUser || !$ghToken) {
            throw new \RuntimeException('GH_USER or GJ_TOKEN is not defined. Please see README');
        }

        $injector
            ->share(ZenHubClient::class)
            ->define(ZenHubClient::class, [':apiToken' => $_ENV['ZH_TOKEN']])
            ->share(GitHubClient::class)
            ->define(
                GitHubClient::class,
                [':username' => $_ENV['GH_USER'], ':token' => $_ENV['GH_TOKEN']]
            )
            ->share(YouTrackClient::class)
            ->delegate(
                YouTrackClient::class,
                function () use ($injector, $ytToken, $ytDomain): YouTrackClient {
                    $handlerStack = HandlerStack::create();
                    $handlerStack->push(Middleware::retry(
                        function ($retries, RequestInterface $request, ResponseInterface $response) {
                            if ($retries > 10)  {
                                var_dump($request);
                                $request->getBody()->rewind();
                                var_dump($request->getBody()->getContents());

                                return false;
                            }
                            return $response->getStatusCode() === 403;
                        },
                        function ($retries) {
                            return $retries * 1000;
                        }
                    ));

                    $guzzle = new Client(
                        [
                            'handler' => $handlerStack,
                            'base_uri' => "https://{$ytDomain}/youtrack/"
                        ]
                    );
                    $client = new HttpClient\GuzzleHttpClient($guzzle);
                    $auth = new TokenAuthorizer($ytToken);

                    return new YouTrackClient($client, $auth);
                }
            );
    }
}
