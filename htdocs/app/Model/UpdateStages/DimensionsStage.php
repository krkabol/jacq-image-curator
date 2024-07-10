<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\StorageConfiguration;
use GuzzleHttp\Client;
use League\Pipeline\StageInterface;


class DimensionStageException extends BaseStageException
{

}

class DimensionsStage implements StageInterface
{
    protected Client $client;
    protected StorageConfiguration $configuration;

    public function __construct(Client $client, StorageConfiguration $configuration)
    {
        $this->client = $client;
        $this->configuration = $configuration;
    }

    public function __invoke($payload)
    {
        /** @var Photos $payload */
        $data = $this->getInfoFromImageServer($payload->getJp2Filename());

        if (isset($data['width'])) {
            $payload->setWidth($data['width']);
        } else {
            throw new DimensionStageException('Parameter "width" not found.');
        }
        if (isset($data['height'])) {
            $payload->setHeight($data['height']);
        } else {
            throw new DimensionStageException('Parameter "height" not found.');
        }
        return $payload;
    }

    protected function getInfoFromImageServer($url): array
    {
        try {
            $response = $this->client->request('GET', $this->configuration->getImageIIIFInfoURL($url));
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new DimensionStageException("Expected JP2 file is missing");
            }
            $body = (string)$response->getBody();
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new DimensionStageException("Error during decoding JSON response: " . json_last_error_msg());
            }
            return $data;
        } catch (DimensionStageException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DimensionStageException("Problem to detect JP2 dimensions: " . $e->getMessage());
        }
    }
}
