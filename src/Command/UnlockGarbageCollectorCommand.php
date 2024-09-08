<?php

declare(strict_types=1);

namespace AM\InterventionRequest\Command;

use AM\InterventionRequest\Cache\GarbageCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnlockGarbageCollectorCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('gc:unlock')
            ->setDescription('Unlock Intervention Request garbage collector')
            ->addArgument(
                'cache',
                InputArgument::REQUIRED,
                'Cache directory path'
            )
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = $input->getArgument('cache');
        $text = "";

        if (is_string($cacheDir) && file_exists($cacheDir)) {
            $gc = new GarbageCollector($cacheDir);
            if (file_exists($gc->getLockPath())) {
                unlink($gc->getLockPath());
            }
            $text .= "<info>Garbage collection unlocked.</info>" . PHP_EOL;
        } else {
            $text .= "<error>Cache directory does not exist.</error>" . PHP_EOL;
        }

        $output->writeln($text);
        return 0;
    }
}
