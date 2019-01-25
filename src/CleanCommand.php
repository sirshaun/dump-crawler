<?php

namespace DumpCrawler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $project = $input->getArgument('project');
        $output->writeln('<comment>Starting search in project: '.basename($project).' ...</comment>');
        $fileSearch = new FileSearch($project, $output);
        $fileSearch->run();
    }
}
