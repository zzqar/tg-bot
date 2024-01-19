<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterWebhookCommand extends Command
{
    protected static $defaultName = 'bot:register';
    protected static $defaultDescription = 'Register bot webhook';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("sending:");
        $output->writeln("https://api.telegram.org/bot{$_ENV['TG_BOT_SECRET']}/setWebhook?url={$_ENV['HTTP_HOST']}/bot/incoming");

        $result = (new Client([
            'base_uri' => 'https://api.telegram.org'
        ]))
            ->get("/bot{$_ENV['TG_BOT_SECRET']}/setWebhook?url={$_ENV['HTTP_HOST']}/bot/incoming")
            ->getBody()
            ->getContents();
        $output->writeln($result);

        return Command::SUCCESS;
    }
}
