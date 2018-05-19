<?php

namespace App\Command\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class CreateUserCommand extends Command
{
    /** @var SymfonyStyle */
    private $io;

    protected function configure()
    {
        $this
            ->setName('rabbit:create:user')
            ->setDescription('Create new RabbitMQ user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->enableRabbitManagementPlugin();
        $username = $this->createNewUser();
        $this->grantUserPermissions($username);

        $this->io->success('user successfully created');
    }

    private function enableRabbitManagementPlugin()
    {
        $this->io->title('Enabling rabbitmq_management');

        $process = new Process('sudo rabbitmq-plugins enable rabbitmq_management');
        $process->run();
    }

    private function createNewUser(): string
    {
        $this->io->title('Creating new user');

        $username = $this->askUsername();
        $password = $this->askPassword();

        $process = new Process(sprintf(
            'sudo rabbitmqctl add_user %s %s',
            $username,
            $password
        ));
        $process->run();

        return $username;
    }

    private function grantUserPermissions(string $username)
    {
        $this->io->title('Admin permissions');

        $process = new Process(sprintf('sudo rabbitmqctl set_user_tags %s administrator', $username));
        $process->run();

        $this->io->title('Grant permissions for all VirtualHosts');

        $process = new Process(sprintf('sudo rabbitmqctl set_permissions -p / %s ".*" ".*" ".*"', $username));
        $process->run();
    }

    protected function askUsername(): string
    {
        return $this->io->ask('Enter a username (enter for default `admin` user)', 'admin');
    }

    protected function askPassword(): string
    {
        // ask password.
        $password = $this->io->askHidden('Enter new password', function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Password cannot be blank.');
            }
            return $value;
        });

        // ask password confirmation.
        $this->io->askHidden('Confirm new password', function ($repeated) use ($password) {
            if (empty($repeated)) {
                throw new \RuntimeException('Password cannot be blank.');
            }
            if ($password !== $repeated) {
                throw new \RuntimeException('Passwords do not match.');
            }
            return $repeated;
        });

        return $password;
    }
}
