<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebhookStatusCommand extends Command
{
    protected static $defaultName = 'bot:status';
    protected static $defaultDescription = 'webhook status';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = (new Client([
            'base_uri' => 'https://api.telegram.org'
        ]))
            ->get("/bot{$_ENV['TG_BOT_SECRET']}/getWebhookInfo")
            ->getBody()
            ->getContents();
        $output->writeln($result);

        return Command::SUCCESS;
    }
}
