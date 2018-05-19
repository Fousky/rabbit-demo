<?php

namespace App\Command\Producer;

use App\Client\RabbitClientAdapterFactory;
use App\Model\ProducerMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class ProduceEmailCommand extends Command
{
    /** @var RabbitClientAdapterFactory */
    private $clientAdapterFactory;

    public function __construct(
        RabbitClientAdapterFactory $managementClientAdapterFactory,
        string $name = null
    ) {
        $this->clientAdapterFactory = $managementClientAdapterFactory;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('rabbit:produce:email')
            ->setDescription('Send e-mail producer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientAdapter = $this->clientAdapterFactory->getAdapter();

        $io = new SymfonyStyle($input, $output);
        $limit = (int) $io->ask('Number of e-mails?', 1);
        $from = (string) $io->ask('E-mail FROM', 'sample@sample.com');
        $to = (string) $io->ask('E-mail TO', 'sample@sample.com');
        $subject = (string) $io->ask('E-mail SUBJECT', 'Hello world');

        $routingKey = (string) $io->ask('RabbitMQ message `routing_key`? ', 'email');

        $progress = $io->createProgressBar($limit);
        $progress->setRedrawFrequency(10);
        $progress->start();

        for ($x = 1; $x <= $limit; $x++) {
            $message = new ProducerMessage();
            $message->setRoutingKey($routingKey);
            $message->setBody([
                'subject' => $subject,
                'content' => '<html><body><h1>Hello world.</h1></body></html>',
                'from' => $from,
                'to' => $to,
            ]);

            $clientAdapter->publish($message);

            $progress->advance();
        }

        $progress->finish();

        $io->newLine(2);
        $io->success(sprintf('Produced %d messages.', $limit));
    }
}
