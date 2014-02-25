<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli\Commands;

use KeePassCli\EntityListBuilder;
use \KeePassCli\Helper\EntityTableHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GroupInfo extends Command implements ApplicationInterface
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
            ->setName('group:info')
            ->setDescription('Lists info about selected group');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var DialogHelper  $dialog */
        $dialog         = $this->getHelperSet()->get('dialog');
        /** @var EntityTableHelper $entityTable */
        $entityTable    = $this->getHelperSet()->get('entity_table');
        /** @var \KeePass\EntityController\Controller $ec */
        $ec             = $this->application->get('keepass')->getEntityController();
        $ac             = new EntityListBuilder($ec, 'group');
        $index          = $ac->build(array('name','namespace'));
        $name           = $dialog->ask($output, 'Server: ', null, $index);
        $results        = $ac->getResultByIndex($name);

        $entityTable->setHeaders(array('Name','Value'));

        foreach ( $results as $result ){

            if ( false !== $entry = $ec->getEntities('group')->where('uuid', $result)->getSingleResult()) {
                if ($entry) {
                    $entityTable->addEntity($entry);
                    $entityTable->render($output);
                }
            }
        }
    }

}
 