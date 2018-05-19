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
class CreateQueueCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $clientAdapterFactory;

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
            ->setName('rabbit:create:queue')
            ->setDescription('Create new Queue')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $clientAdapter = $this->clientAdapterFactory->getAdapter();

        $vhost = $this->askForVhost($clientAdapter);
        $queue = (string) $this->io->ask('Enter Queue name');
        $optionAutoDelete = $this->io->confirm('Enable option `auto_delete`?', false);
        $optionDurable = $this->io->confirm('Enable option `durable`?', true);

        // call API and create Queue for selected VirtualHost
        $clientAdapter->createQueue(
            $vhost,
            $queue,
            [
                'auto_delete' => $optionAutoDelete,
                'durable' => $optionDurable,
            ]
        );
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
}
