<?php declare(strict_types = 1);

namespace App\Model\MigrationStages;

use App\Model\MigrationStages\Exceptions\Jp2ExistsException;
use App\Services\StorageConfiguration;
use GuzzleHttp\Client;
use League\Pipeline\StageInterface;

class JP2ExistsStage implements StageInterface
{

    protected StorageConfiguration $configuration;

    protected Client $client;

    public function __construct(StorageConfiguration $configuration, Client $client)
    {
        $this->configuration = $configuration;
        $this->client = $client;
    }

    protected function checkJp2fromImageServer(string $url): void
    {
        try {
            $response = $this->client->request('GET', $this->configuration->getImageIIIFInfoURL($url));
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new Jp2ExistsException('Expected JP2 file is missing');
            }
        } catch (Jp2ExistsException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new Jp2ExistsException('Problem to detect JP2 presence: ' . $e->getMessage());
        }
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->checkJp2fromImageServer($payload->getJp2Filename());

        return $payload;
    }

}
