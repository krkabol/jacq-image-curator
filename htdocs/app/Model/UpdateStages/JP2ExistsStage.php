<?php

declare(strict_types=1);

namespace app\Model\UpdateStages;

use app\Model\Database\Entity\Photos;
use app\Services\StorageConfiguration;
use GuzzleHttp\Client;
use League\Pipeline\StageInterface;


class JP2ExistsException extends BaseStageException
{

}

class JP2ExistsStage implements StageInterface
{

    protected StorageConfiguration $configuration;
    protected Client $client;

    public function __construct(StorageConfiguration $configuration, Client $client)
    {
        $this->configuration = $configuration;
        $this->client = $client;
    }

    public function __invoke($payload)
    {
        /** @var Photos $payload */
        $this->checkJP2fromImageServer($payload->getJp2Filename());
        return $payload;
    }

    protected function checkJP2fromImageServer($url)
    {
        try {
            $response = $this->client->request('GET', $this->configuration->getImageIIIFInfoURL($url));
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                throw new JP2ExistsException("Expected JP2 file is missing");
            }
        } catch (JP2ExistsException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new JP2ExistsException("Problem to detect JP2 presence: " . $e->getMessage());
        }
    }
}
