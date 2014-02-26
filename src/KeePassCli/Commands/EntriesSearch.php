<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use \KeePassCli\EntityListBuilder;
use \KeePassCli\Helper\EntityTableHelper;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Helper\DialogHelper;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;


class EntriesSearch extends Command implements ApplicationInterface
{
    /** @var \KeePass\Application  */
    protected $application;

    /**
     * @param \KeePass\Application $application
     */
    public function setKeePassApplication(\KeePass\Application $application)
    {
        $this->application = $application;
    }

    protected function configure()
    {
        $this
            ->setName('entries:search')
            ->setDescription('Do a search on entries')
            ->addArgument('search', InputArgument::OPTIONAL, 'Who do you want to search?')
            ->addOption('field', 'f', InputOption::VALUE_REQUIRED, 'Property field to search in')
            ->addOption('case-insensitive', 'i', InputOption::VALUE_NONE, 'Set to search case insensitive')
            ->setHelp("
                If option field is left empty it will search on all property fields.

                You can do wild cards search with '%' sign on en end/begin or both on string
                So for example:

                if you search: '%site.nl' it will return all entities where property fields
                end with site.nl, similar to mysql like function only here you can use preg
                syntax as wel. So '%site.(nl|org|com)' should also world

            ");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper  $dialog */
        $dialog      = $this->getHelperSet()->get('dialog');
        /** @var EntityTableHelper $entityTable */
        $entityTable = $this->getHelperSet()->get('entity_table');
        /** @var \KeePass\EntityController\Controller $ec */
        $ec          = $this->application->get('keepass')->getEntityController();
        /** @var \KeePass\EntityController\Filters\Entry $entities */
        $entities    = $ec->getEntities('entry');
        $search      = $input->getArgument('search');
        $field       = $input->getOption('field');
        $ci          = $input->getOption('case-insensitive');

        if (empty($search)) {
            $search = $dialog->ask($output, 'Search: ');
        }

        if (!empty($field)) {
            $results = $entities->where($field, $search, 'like', $ci)->getResult();
        } else {
            $results = $entities->search($search, $ci)->getResult();
        }

        $entityTable->setHeaders(array('Name','Value'));

        foreach ($results as $entry) {
            if ($entry) {
                $entityTable->addEntity($entry);
                $entityTable->render($output);
            }
        }

        $output->writeln(sprintf("\nFound <comment>%s</comment> records\n",count($results)));


    }

}