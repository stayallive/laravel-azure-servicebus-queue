<?php

namespace Stayallive\LaravelAzureServicebusQueue;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Container\Container;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;

class AzureJob extends Job
{

    /**
     * The Azure IServiceBus instance.
     *
     * @var \WindowsAzure\ServiceBus\Internal\IServiceBus
     */
    protected $azure;

    /**
     * The Azure ServiceBus job instance.
     *
     * @var \WindowsAzure\ServiceBus\Models\BrokeredMessage
     */
    protected $job;

    /**
     * The queue that the job belongs to.
     *
     * @var string
     */
    protected $queue;

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Container\Container                 $container
     * @param \WindowsAzure\ServiceBus\Internal\IServiceBus   $azure
     * @param \WindowsAzure\ServiceBus\Models\BrokeredMessage $job
     * @param  string                                         $queue
     *
     * @return \Stayallive\LaravelAzureServicebusQueue\AzureJob
     */
    public function __construct(Container $container, IServiceBus $azure, BrokeredMessage $job, $queue)
    {
        $this->azure     = $azure;
        $this->job       = $job;
        $this->queue     = $queue;
        $this->container = $container;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->getRawBody(), true));
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        $this->azure->deleteMessage($this->job);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     *
     * @return void
     */
    public function release($delay = 0)
    {
        $release = new \DateTime;
        $release->setTimezone(new \DateTimeZone('UTC'));
        $release->add(new \DateInterval('PT' . $delay . 'S'));

        $this->job->setScheduledEnqueueTimeUtc($release);

        $this->azure->unlockMessage($this->job);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return $this->job->getDeliveryCount();
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying Azure client instance.
     *
     * @return \WindowsAzure\ServiceBus\Internal\IServiceBus
     */
    public function getAzure()
    {
        return $this->azure;
    }

    /**
     * Get the underlying raw Azure job.
     *
     * @return \WindowsAzure\ServiceBus\Models\BrokeredMessage
     */
    public function getAzureJob()
    {
        return $this->job;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->getBody();
    }
}
