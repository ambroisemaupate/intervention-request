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

class GarbageCollectorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('gc:launch')
            ->setDescription('Launch Intervention Request garbage collector')
            ->addArgument(
                'cache',
                InputArgument::OPTIONAL,
                'Cache directory path',
                getenv('IR_CACHE_PATH') ?: null
            )
            ->addOption(
                'log',
                'l',
                InputOption::VALUE_REQUIRED,
                'Log file path',
                './interventionRequest.log'
            )
            ->addOption(
                'ttl',
                't',
                InputOption::VALUE_REQUIRED,
                'Time to live to set to the garbage collector.',
                getenv('IR_GC_TTL') ?: null
            )
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = $input->getArgument('cache');
        $logFile = $input->getOption('log');
        $ttl = $input->getOption('ttl');
        $text = '';

        if (is_string($logFile) && !empty($logFile)) {
            $log = new Logger('InterventionRequest');
            $log->pushHandler(new StreamHandler($logFile, Level::Info));
        } else {
            $log = new NullLogger();
        }

        if (is_string($cacheDir) && !empty($cacheDir) && file_exists($cacheDir)) {
            if (is_numeric($ttl)) {
                $gc = new GarbageCollector($cacheDir, $log, (int) $ttl);
            } else {
                $gc = new GarbageCollector($cacheDir, $log);
            }

            $text .= '<info>Garbage collection started on '.$cacheDir.' for TTL '.$gc->getTtl().'.</info>'.PHP_EOL;
            $gc->launch();
            $text .= '<info>Garbage collection finished.</info>'.PHP_EOL;
        } else {
            $text .= '<error>Cache directory does not exist.</error>'.PHP_EOL;
        }

        $output->writeln($text);

        return 0;
    }
}
