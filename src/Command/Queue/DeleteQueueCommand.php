<?php

namespace App\Command\Queue;

use App\Client\RabbitManagementClientAdapter;
use App\Client\RabbitManagementClientAdapterFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class DeleteQueueCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $managementClientAdapterFactory;

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
            ->setName('rabbit:delete:queue')
            ->setDescription('Delete Queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $managementClientAdapter = $this->managementClientAdapterFactory->getAdapter();

        $vhost = $this->askForVhost($managementClientAdapter);
        $queue = $this->askForQueue($managementClientAdapter, $vhost);

        if (!$io->confirm(sprintf('Really delete Queue %s?', $queue), false)) {
            $io->warning('Skipping.');

            return;
        }

        $managementClientAdapter->deleteQueue($vhost, $queue);
    }

    private function askForVhost(RabbitManagementClientAdapter $clientAdapter): string
    {
        // call API and get available VirtualHosts
        $virtualHosts = $clientAdapter->getVirtualHosts();
        $virtualHostsChoices = array_map(function (array $row) {
            return $row['name'];
        }, $virtualHosts);

        if (count($virtualHostsChoices) === 0) {
            throw new \RuntimeException('No VirtualHosts.');
        }

        return count($virtualHostsChoices) === 1
            ? array_shift($virtualHostsChoices)
            : $this->io->choice('Chooce VirtualHost', $virtualHostsChoices);
    }

    private function askForQueue(RabbitManagementClientAdapter $clientAdapter, string $vhost): string
    {
        $queues = $clientAdapter->getQueues($vhost);
        $queuesChoices = array_map(function (array $row) {
            return $row['name'];
        }, $queues);

        if (count($queuesChoices) === 0) {
            throw new \RuntimeException('No Queues');
        }

        return $this->io->choice('Choose Queue', $queuesChoices);
    }
}
