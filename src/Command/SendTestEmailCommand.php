<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:send-test-email',
    description: 'Envía un correo electrónico de prueba',
)]
class SendTestEmailCommand extends Command
{
    private $mailer;

    // Inyectamos MailerInterface a través del constructor
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envía un correo electrónico de prueba')
            ->setHelp('Este comando te permite enviar un correo electrónico de prueba a una dirección preconfigurada.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Crear el correo
        $email = (new Email())
            ->from('josuej.salazar@gmail.com')
            ->to('josuej.salazar@gmail.com')
            ->subject('Correo de prueba de Symfony')
            ->text('Este es un correo de prueba enviado desde un comando de Symfony.');

        // Intentar enviar el correo
        try {
            $this->mailer->send($email);
            $output->writeln('<info>Correo electrónico enviado con éxito.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error al enviar el correo: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}