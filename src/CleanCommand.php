<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CleanCommand extends Command
{
    protected function configure()
    {
        $this->setName('clean')
            ->setDescription('remove trailing dd\'s from code')
            ->addArgument('project', InputArgument::REQUIRED, 'Which project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Regular expression pattern to find dd's
        $reg = '/^[^(\\\\|#)](\t|\s)*dd\(.*\);$/m';
        $project = $input->getArgument('project');

        $finder = new Finder;

        $output->writeln('<comment>Searching through project...</comment>');
        $files = $finder
            ->files()
            ->in($project)
            ->notPath('vendor', 'node_modules') // ignore vendor & node_modules folders
            ->contains($reg);

        $progressBar = new ProgressBar($output, count($files));

        $references = [];
        $progressBar->start();
        foreach ($files as $file) {
            $name = basename($file->getRealPath());
            $contents = $file->getContents();
            $referenceLines = [];

            // Find the exact lines of the matches ...
            preg_match_all($reg, $contents, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $arr) {
                if (!empty(trim($arr[0]))) {
                    $line = 1 + substr_count($contents, "\n", 0, $arr[1]);
                    $referenceLines[] = ['line' => $line, 'content' => trim($arr[0])];
                }
            }

            // Note the refernces ...
            $references[$name] = $referenceLines;
            $progressBar->advance();
        }
        $progressBar->finish();
        echo PHP_EOL;
        echo PHP_EOL;

        // Render the output ...
        if (!empty($references)) {
            $output->writeln("<error>Found trailing dd's!</error>");

            foreach ($references as $key => $arr) {
                $table = new Table($output);
                $output->writeln("<--------------------------------------------------------------------");
                $output->writeln('<comment>'.$key.'</comment>');
                $table->setHeaders(['Line', 'Content'])
                    ->setRows($arr)
                    ->render();
                $output->writeln("-------------------------------------------------------------------->");
            }
        } else {
            $output->writeln("<info>No trailing dd's found!</info>");
        }
    }
}