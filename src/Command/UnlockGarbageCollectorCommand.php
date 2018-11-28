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
 * @file UnlockGarbageCollectorCommand.php
 * @author Ambroise Maupate
 */
namespace AM\InterventionRequest\Command;

use AM\InterventionRequest\Cache\GarbageCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnlockGarbageCollectorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('gc:unlock')
            ->setDescription('Unlock garbage collector')
            ->addArgument(
                'cache',
                InputArgument::REQUIRED,
                'Cache directory path'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = $input->getArgument('cache');
        $text = "";

        if (file_exists($cacheDir)) {
            $gc = new GarbageCollector($cacheDir);
            if (file_exists($gc->getLockPath())) {
                unlink($gc->getLockPath());
            }
            $text .= "<info>Garbage collection unlocked.</info>" . PHP_EOL;
        } else {
            $text .= "<error>Cache directory does not exist.</error>" . PHP_EOL;
        }

        $output->writeln($text);
    }
}
