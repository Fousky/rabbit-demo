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
final class DeleteBindingCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $managementClientAdapterFactory;

    /** @var \App\Client\RabbitManagementClientAdapter */
    private $managementClientAdapter;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        RabbitManagementClientAdapterFactory $managementClientAdapterFactory,
        string $name = null
    ) {
        $this->managementClientAdapterFactory = $managementClientAdapterFactory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('rabbit:delete:binding')
            ->setDescription('Delete binding for given Virtual host and Queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->managementClientAdapter = $this->managementClientAdapterFactory->getAdapter();
        $this->io = new SymfonyStyle($input, $output);

        $vhost = $this->askForVhost();
        $queue = $this->askForQueue($vhost);

        $this->askAndDeleteBindings($vhost, $queue);
    }

    private function askAndDeleteBindings(string $vhost, string $queue)
    {
        $this->managementClientAdapter->deleteBindings(
            $vhost,
            $this->io->choice('Choose Exchange', ProducerMessage::$exchanges),
            $queue,
            $this->io->ask('Enter routing key')
        );
    }

    private function askForVhost(): string
    {
        // call API and get available VirtualHosts
        $virtualHosts = $this->managementClientAdapter->getVirtualHosts();
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
        $queues = $this->managementClientAdapter->getQueues($vhost);
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
