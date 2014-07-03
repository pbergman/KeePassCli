<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use \KeePassCli\EntityListBuilder;
use \KeePassCli\Helper\EntityTableHelper;
use PBergman\KeePass\Entity\Entry;
use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Helper\DialogHelper;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

use \PBergman\WhipTail\Controller as ShellDialog;


class EntriesInfo extends Command implements ApplicationInterface
{
    /** @var \PBergman\KeePass\Application  */
    protected $application;

    /**
     * @param \PBergman\KeePass\Application $application
     */
    public function setKeePassApplication(\PBergman\KeePass\Application $application)
    {
        $this->application = $application;
    }

    protected function configure()
    {
        $this
            ->setName('entries:info')
            ->setDescription('Lists info about selected entry');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper  $dialog */
        $dialog         = $this->getHelperSet()->get('dialog');
        /** @var EntityTableHelper $entityTable */
        $entityTable    = $this->getHelperSet()->get('entity_table');
        /** @var \PBergman\KeePass\EntityController\Controller $ec */
        $ec             = $this->application->get('keepass')->getEntityController();
        $ac             = new EntityListBuilder($ec, 'entry');
        $index          = $ac->build(array('name','namespace'));
        $name           = $dialog->ask($output, 'Group: ', null, $index);
        $results        = $ac->getResultByIndex($name);


        $entityTable->setHeaders(array('Name','Value'));

        foreach ($results as $result) {
            if ( false !== $entry = $ec->getEntities('entry')->where('uuid', $result)->getSingleResult()) {
                if ($entry) {
                    $entityTable->addEntity($entry);
                    $entityTable->render($output);
                }
            }
        }

    }

}