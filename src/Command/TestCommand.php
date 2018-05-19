<?php

namespace App\Command;

use App\Client\RabbitManagementClientAdapterFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class TestCommand extends Command
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
            ->setName('rabbit:test')
            ->setDescription('Execute connection test')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->testManagementApi();
    }

    private function testManagementApi()
    {
        $managementClientAdapter = $this->managementClientAdapterFactory->getAdapter();

        try {
            $result = $managementClientAdapter->whoami();

            if (is_array($result) && array_key_exists('name', $result)) {
                $this->io->success('Management API connection was established.');
            } else {
                $this->io->error(sprintf('Management API authentification error.'));
            }
        } catch (\Exception $e) {
            $this->io->error(sprintf('Management API connection error: %s', $e->getMessage()));
        }
    }
}
