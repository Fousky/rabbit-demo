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
final class CreateVirtualHostCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $managementClientAdapterFactory;

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
            ->setName('rabbit:create:virtualhost')
            ->setDescription('Create new Virtual host')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $managementClientAdapter = $this->managementClientAdapterFactory->getAdapter();
        $managementClientAdapter->createVirtualHost(
            $io->ask('Enter VirtualHost name')
        );
    }
}
