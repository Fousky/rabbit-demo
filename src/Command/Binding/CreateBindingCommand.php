<?php

namespace App\Command\Binding;

use App\Client\RabbitManagementClientAdapterFactory;
use App\Model\ProducerMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class CreateBindingCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $clientAdapterFactory;

    /** @var \App\Client\RabbitManagementClientAdapter */
    private $clientAdapter;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        RabbitManagementClientAdapterFactory $clientAdapterFactory,
        string $name = null
    ) {
        $this->clientAdapterFactory = $clientAdapterFactory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('rabbit:create:binding')
            ->setDescription('Create a new Exchange > Queue `binding`.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clientAdapter = $this->clientAdapterFactory->getAdapter();
        $this->io = new SymfonyStyle($input, $output);

        $vhost = $this->askForVhost();
        $queue = $this->askForQueue($vhost);

        $this->askAndCreateBindings($vhost, $queue);
    }

    private function askAndCreateBindings(string $vhost, string $queue)
    {
        $this->io->note(sprintf('Virtual host: %s', $vhost));
        $this->io->note(sprintf('Queue: %s', $queue));

        $this->clientAdapter->createBindings(
            $vhost,
            $this->io->choice('Choose Exchange', ProducerMessage::$exchanges),
            $queue,
            $this->io->ask('Enter routing key')
        );
    }

    private function askForVhost(): string
    {
        // call API and get available VirtualHosts
        $virtualHosts = $this->clientAdapter->getVirtualHosts();
        $virtualHostsChoices = array_map(function (array $row) {
            return $row['name'];
        }, $virtualHosts);

        if (count($virtualHostsChoices) === 0) {
            throw new \RuntimeException('No VirtualHosts.');
        }

        return count($virtualHostsChoices) === 1
            ? array_shift($virtualHostsChoices)
            : $this->io->choice('Choose VirtualHost', $virtualHostsChoices);
    }

    private function askForQueue(string $vhost)
    {
        $queues = $this->clientAdapter->getQueues($vhost);
        $queuesChoices = array_map(function (array $queue) {
            return $queue['name'];
        }, $queues);

        if (count($queuesChoices) === 0) {
            throw new \RuntimeException('No Queues.');
        }

        return count($queuesChoices) === 1
            ? array_shift($queuesChoices)
            : $this->io->choice('Choose Queue', $queuesChoices);
    }
}
