<?php

namespace Stayallive\LaravelAzureServicebusQueue;

use Illuminate\Http\Request;
use WindowsAzure\Common\ServicesBuilder;
use Illuminate\Queue\Connectors\ConnectorInterface;

class AzureConnector implements ConnectorInterface
{

    /**
     * Establish a queue connection.
     *
     * @param  array $config
     *
     * @return \Illuminate\Queue\QueueInterface
     */
    public function connect(array $config)
    {
        $connectionString = 'Endpoint=' . $config['endpoint'] . ';SharedSecretIssuer=' . $config['secretissuer'] . ';SharedSecretValue=' . $config['secret'];
        $serviceBusRestProxy = ServicesBuilder::getInstance()->createServiceBusService($connectionString);

        return new AzureQueue($serviceBusRestProxy, $config['queue']);
    }
}
