<?php

namespace App\Command\Consumer;

use App\Client\RabbitClientAdapterFactory;
use App\Client\RabbitManagementClientAdapterFactory;
use Bunny\Channel;
use Bunny\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class ConsumeEmailCommand extends Command
{
    /** @var RabbitManagementClientAdapterFactory */
    private $managementClientAdapterFactory;

    /** @var RabbitClientAdapterFactory */
    private $clientAdapterFactory;

    /** @var \App\Client\RabbitManagementClientAdapter */
    private $managementClientAdapter;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        RabbitManagementClientAdapterFactory $managementClientAdapterFactory,
        RabbitClientAdapterFactory $clientAdapterFactory,
        string $name = null
    ) {
        $this->managementClientAdapterFactory = $managementClientAdapterFactory;
        $this->clientAdapterFactory = $clientAdapterFactory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('rabbit:consume:email')
            ->setDescription('Consume messages from RabbitMQ')
            ->addArgument(
                'virtual-host',
                InputArgument::OPTIONAL,
                'Listen for given VirtualHost name.'
            )
            ->addArgument(
                'queue',
                InputArgument::OPTIONAL,
                'Listen for given Queue name.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureEnvironment($input, $output);

        $vhost = $this->askForVhost($input->getArgument('virtual-host'));
        $queue = $this->askForQueue($vhost, $input->getArgument('queue'));

        $rabbitClientAdapter = $this->clientAdapterFactory->getAdapter($vhost);
        $channel = $rabbitClientAdapter->getChannel();

        $this->io->progressStart();

        $totalMessages = 0;

        /**
         * configure QoS -> number of prefetched messages while new messages income to RabbitMQ
         */
        $channel->qos(0, 1);

        /**
         * Listen for Messages at Channel in RabbitMQ
         */
        $channel->run(
            function (Message $message, Channel $channel) use (&$totalMessages) {
                /**
                 * TODO: do something - e.g. send e-mail
                 */
                $totalMessages++;

                $this->io->progressAdvance();
                $this->io->write(sprintf("\t [message number %d]", $totalMessages));

                $channel->ack($message);
            },
            $queue
        );
    }

    private function askForVhost(string $argumentVhost = null): string
    {
        // call API and get available VirtualHosts
        $virtualHosts = $this->managementClientAdapter->getVirtualHosts();
        $virtualHostsChoices = array_map(function (array $row) {
            return $row['name'];
        }, $virtualHosts);

        if (count($virtualHostsChoices) === 0) {
            throw new \RuntimeException('No VirtualHosts.');
        }

        if ($argumentVhost !== null) {
            if (!in_array($argumentVhost, $virtualHostsChoices, true)) {
                throw new \RuntimeException(sprintf('Vhost `%s` does not exists.', $argumentVhost));
            }

            return $argumentVhost;
        }

        return count($virtualHostsChoices) === 1
            ? array_shift($virtualHostsChoices)
            : $this->io->choice('Choose VirtualHost', $virtualHostsChoices);
    }

    private function askForQueue(string $vhost, string $argumentQueue = null)
    {
        $queues = $this->managementClientAdapter->getQueues($vhost);
        $queuesChoices = array_map(function (array $queue) {
            return $queue['name'];
        }, $queues);

        if (count($queuesChoices) === 0) {
            throw new \RuntimeException('No Queues.');
        }

        if ($argumentQueue !== null) {
            if (!in_array($argumentQueue, $queuesChoices, true)) {
                throw new \RuntimeException(sprintf('Queue `%s` does not exists.', $argumentQueue));
            }

            return $argumentQueue;
        }

        return count($queuesChoices) === 1
            ? array_shift($queuesChoices)
            : $this->io->choice('Choose Queue', $queuesChoices);
    }

    private function configureEnvironment(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->managementClientAdapter = $this->managementClientAdapterFactory->getAdapter();
    }
}
