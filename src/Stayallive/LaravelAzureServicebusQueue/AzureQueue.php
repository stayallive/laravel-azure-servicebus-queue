<?php

namespace Stayallive\LaravelAzureServicebusQueue;

use Illuminate\Queue\Queue;
use Illuminate\Http\Request;
use Illuminate\Queue\QueueInterface;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\Models\ReceiveMessageOptions;

class AzureQueue extends Queue implements QueueInterface
{

    /**
     * The Azure IServiceBus instance.
     *
     * @var \WindowsAzure\ServiceBus\Internal\IServiceBus
     */
    protected $azure;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * Create a new Azure IQueue queue instance.
     *
     * @param  \WindowsAzure\ServiceBus\Internal\IServiceBus $azure
     * @param  string                                        $default
     *
     * @return \Stayallive\LaravelAzureServicebusQueue\AzureQueue
     */
    public function __construct(IServiceBus $azure, $default)
    {
        $this->azure   = $azure;
        $this->default = $default;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string $job
     * @param  mixed  $data
     * @param  string $queue
     *
     * @return void
     */
    public function push($job, $data = '', $queue = null)
    {
        $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array  $options
     *
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = array())
    {
        $message = new BrokeredMessage($payload);

        $this->azure->sendQueueMessage($this->getQueue($queue), $message);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  int    $delay
     * @param  string $job
     * @param  mixed  $data
     * @param  string $queue
     *
     * @return void
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data);

        $release = new \DateTime;
        $release->setTimezone(new \DateTimeZone('UTC'));
        $release->add(new \DateInterval('PT' . $delay . 'S'));

        $message = new BrokeredMessage($payload);
        $message->setScheduledEnqueueTimeUtc($release);

        $this->azure->sendQueueMessage($this->getQueue($queue), $message);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     *
     * @return \Illuminate\Queue\Jobs\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $options = new ReceiveMessageOptions;
        $options->setPeekLock();

        $job = $this->azure->receiveQueueMessage($queue, $options);

        if (!is_null($job)) {
            return new AzureJob($this->container, $this->azure, $job, $queue);
        }
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null $queue
     *
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Get the underlying Azure IQueue instance.
     *
     * @return \WindowsAzure\Queue\Internal\IQueue
     */
    public function getAzure()
    {
        return $this->azure;
    }
}
