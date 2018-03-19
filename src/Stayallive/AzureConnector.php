<?php

namespace Stayallive\LaravelAzureServicebusQueue;

use WindowsAzure\Common\ServicesBuilder;
use Illuminate\Queue\Connectors\ConnectorInterface;

class AzureConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param array $config
     *
     * @return \Illuminate\Queue\QueueInterface
     */
    public function connect(array $config)
    {
        $connectionString = 'Endpoint=' . $config['endpoint'] . ';SharedAccessKeyName=' . $config['sharedAccessKeyName'] . ';SharedAccessKey=' . $config['sharedAccessKey'];

        return new AzureQueue(
            ServicesBuilder::getInstance()->createServiceBusService($connectionString),
            $config['queue']
        );
    }
}
