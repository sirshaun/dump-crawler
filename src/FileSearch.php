<?php

namespace DumpCrawler;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FileSearch
{
    protected $output;
    protected $project;
    protected $counter = PHP_INT_MAX;

    public function __construct(string $project, OutputInterface $output)
    {
        $this->output = $output;
        $this->project = $project;
        $this->reg = '/^[^(\\\\|#)](\t|\s)*dd\(.*\);$/m';
    }

    public function run()
    {
        $references = [];

        // Search through files using finder ...
        $files = $this->findFiles();

        // Build references from files that have been returned ...
        $this->buildReferences($files, $references);

        // Output references ...
        $this->outputReferences($references);
    }

    protected function buildReferences($files, array &$references)
    {
        ProgressBar::setFormatDefinition('custom', '%elapsed:6s% <fg=white;bg=blue>%memory:6s%</>');
        $progressBar = new ProgressBar($this->output, 20);
        $progressBar->setFormat('custom');
        $progressBar->start();

        foreach ($files as $file) {
            $name = basename($file->getRealPath());
            $contents = $file->getContents();
            $lastModified = filemtime($file->getRealPath());
            $referenceLines = [];

            // Find the exact lines of the matches ...
            $this->findInContent($contents, $referenceLines);

            // Note the refernces ...
            $references[$name]['lines'] = $referenceLines;
            // Note file last modified at time
            $references[$name]['lastModified'] = $lastModified;

            $progressBar->advance();
        }
        $progressBar->finish();
        $this->sortFiles($references);
    }

    protected function findInContent(string $contents, array &$referenceLines)
    {
        preg_match_all($this->reg, $contents, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $arr) {
            if (!empty(trim($arr[0]))) {
                $line = 1 + substr_count($contents, "\n", 0, $arr[1]);
                $referenceLines[] = ['line' => $line, 'content' => trim($arr[0])];
            }
        }
    }

    protected function findFiles()
    {
        $finder = new Finder;

        return $finder
            ->files()
            ->in($this->project)
            ->notPath('vendor', 'node_modules') // ignore vendor & node_modules folders
            ->contains($this->reg)
            ->getIterator();
    }

    protected function outputReferences(?array $references)
    {
        echo PHP_EOL;
        echo PHP_EOL;
        // Render the output ...
        if (!empty($references)) {
            $this->output->writeln("<error>Found trailing dd's!</error>");
            foreach ($references as $key => $arr) {
                $this->renderTabulatedData($key, $arr);
            }
        } else {
            $this->output->writeln("<info>No trailing dd's found!</info>");
        }
    }

    protected function renderTabulatedData(string $key, array $arr)
    {
        $lastModified = date("F d Y H:i:s.", $arr['lastModified']);
        $table = new Table($this->output);
        $this->output->writeln("<--------------------------------------------------------------------");
        $this->output->writeln('<comment>' . $key . '</comment>');
        $this->output->writeln('<comment>Last modified: ' . $lastModified . '</comment>');
        $table->setHeaders(['Line', 'Content'])
            ->setRows($arr['lines'])
            ->render();
        $this->output->writeln("-------------------------------------------------------------------->");
    }

    protected function sortFiles(array &$references) // NOTE: Sorts in asc order
    {
        uasort($references, function ($a, $b) {
            if ($a['lastModified'] == $b['lastModified']) {
                return 0;
            }
            return ($a['lastModified'] < $b['lastModified']) ? -1 : 1;
        });
    }
}
