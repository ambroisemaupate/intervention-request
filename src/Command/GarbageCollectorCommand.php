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

class GarbageCollectorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('gc:launch')
            ->setDescription('Launch garbage collector')
            ->addArgument(
                'cache',
                InputArgument::REQUIRED,
                'Cache directory path'
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
                604800
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = $input->getArgument('cache');
        $text = "";
        $log = null;

        if ($input->getOption('log')) {
            $log = new Logger('InterventionRequest');
            $log->pushHandler(new StreamHandler($input->getOption('log'), Logger::INFO));
        }

        if (file_exists($cacheDir)) {
            $text .= "<info>Garbage collection started.</info>" . PHP_EOL;
            $gc = new GarbageCollector($cacheDir, $log);
            if ($input->getOption('ttl')) {
                $gc->setTtl($input->getOption('ttl'));
            }
            $gc->launch();
            $text .= "<info>Garbage collection finished.</info>" . PHP_EOL;
        } else {
            $text .= "<error>Cache directory does not exist.</error>" . PHP_EOL;
        }

        $output->writeln($text);
        return 0;
    }
}
