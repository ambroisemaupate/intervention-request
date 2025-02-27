<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Command;

use AM\InterventionRequest\Cache\GarbageCollector;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnlockGarbageCollectorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('gc:unlock')
            ->setDescription('Unlock Intervention Request garbage collector')
            ->addOption(
                'log',
                'l',
                InputOption::VALUE_REQUIRED,
                'Log file path',
                './interventionRequest.log'
            )
            ->addArgument(
                'cache',
                InputArgument::OPTIONAL,
                'Cache directory path',
                getenv('IR_CACHE_PATH') ?: null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = $input->getArgument('cache');
        $logFile = $input->getOption('log');
        $text = '';

        if (is_string($logFile) && !empty($logFile)) {
            $log = new Logger('InterventionRequest');
            $log->pushHandler(new StreamHandler($logFile, Level::Info));
        } else {
            $log = new NullLogger();
        }

        if (is_string($cacheDir) && file_exists($cacheDir)) {
            $gc = new GarbageCollector($cacheDir, $log);
            if (file_exists($gc->getLockPath())) {
                unlink($gc->getLockPath());
            }
            $text .= '<info>Garbage collection unlocked.</info>'.PHP_EOL;
        } else {
            $text .= '<error>Cache directory does not exist.</error>'.PHP_EOL;
        }

        $output->writeln($text);

        return 0;
    }
}
