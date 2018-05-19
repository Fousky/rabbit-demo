<?php

namespace App\Command\Vhost;

use App\Client\RabbitManagementClientAdapterFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class DeleteVirtualHostCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $adapterFactory;

    public function __construct(
        RabbitManagementClientAdapterFactory $adapterFactory,
        string $name = null
    ) {
        $this->adapterFactory = $adapterFactory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('rabbit:delete:virtualhost')
            ->setDescription('Delete VirtualHost')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $managementClientAdapter = $this->adapterFactory->getAdapter();

        $virtualHosts = $managementClientAdapter->getVirtualHosts();
        $virtualHostsChoices = array_map(function (array $row) {
            return $row['name'];
        }, $virtualHosts);

        // remove global virtual host "/"
        unset($virtualHostsChoices[array_search('/', $virtualHostsChoices, true)]);

        if (count($virtualHostsChoices) === 0) {
            $io->warning('No VirtualHosts.');

            return;
        }

        $managementClientAdapter->deleteVirtualHost(
            $io->choice('Chooce VirtualHost', $virtualHostsChoices)
        );
    }
}
