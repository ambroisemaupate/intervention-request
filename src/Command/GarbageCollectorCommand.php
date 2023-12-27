<?php

/**
 * Copyright © 2018, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file GarbageCollectorCommand.php
 * @author Ambroise Maupate
 */

namespace AM\InterventionRequest\Command;

use AM\InterventionRequest\Cache\GarbageCollector;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package AM\InterventionRequest\Command
 */
class GarbageCollectorCommand extends Command
{
    /**
     * @return void
     */
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = $input->getArgument('cache');
        $logFile = $input->getOption('log');
        $ttl = $input->getOption('ttl');
        $text = "";
        $log = null;

        if (is_string($logFile) && !empty($logFile)) {
            $log = new Logger('InterventionRequest');
            $log->pushHandler(new StreamHandler($logFile, Logger::INFO));
        }

        if (is_string($cacheDir) && !empty($cacheDir) && file_exists($cacheDir)) {
            $gc = new GarbageCollector($cacheDir, $log);
            if (\is_numeric($ttl)) {
                $gc->setTtl(intval($ttl));
            }
            $text .= "<info>Garbage collection started on " . $cacheDir . " for TTL " . $gc->getTtl() . ".</info>" . PHP_EOL;
            $gc->launch();
            $text .= "<info>Garbage collection finished.</info>" . PHP_EOL;
        } else {
            $text .= "<error>Cache directory does not exist.</error>" . PHP_EOL;
        }

        $output->writeln($text);
        return 0;
    }
}
