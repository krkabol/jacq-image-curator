<?php declare(strict_types = 1);

namespace App\Model\MigrationStages;

use App\Model\MigrationStages\Exceptions\DimensionsException;
use App\Services\StorageConfiguration;
use GuzzleHttp\Client;
use League\Pipeline\StageInterface;

class DimensionsStage implements StageInterface
{

    protected Client $client;

    protected StorageConfiguration $configuration;

    public function __construct(Client $client, StorageConfiguration $configuration)
    {
        $this->client = $client;
        $this->configuration = $configuration;
    }

    protected function getInfoFromImageServer(string $url): mixed
    {
        try {
            $response = $this->client->request('GET', $this->configuration->getImageIIIFInfoURL($url));
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new DimensionsException('Expected JP2 file is missing');
            }

            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DimensionsException('Error during decoding JSON response: ' . json_last_error_msg());
            }

            return $data;
        } catch (DimensionsException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new DimensionsException('Problem to detect JP2 dimensions: ' . $e->getMessage());
        }
    }

    public function __invoke(mixed $payload): mixed
    {
        $data = $this->getInfoFromImageServer($payload->getJp2Filename());

        if (isset($data['width'])) {
            $payload->setWidth($data['width']);
        } else {
            throw new DimensionsException('Parameter "width" not found.');
        }

        if (isset($data['height'])) {
            $payload->setHeight($data['height']);
        } else {
            throw new DimensionsException('Parameter "height" not found.');
        }

        return $payload;
    }

}
