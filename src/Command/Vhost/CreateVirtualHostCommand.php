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
class CreateVirtualHostCommand extends Command
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
            ->setName('rabbit:create:virtualhost')
            ->setDescription('Create new Virtual host')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $managementClientAdapter = $this->adapterFactory->getAdapter();
        $managementClientAdapter->createVirtualHost(
            $io->ask('Enter VirtualHost name')
        );
    }
}