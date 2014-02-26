<?php
/**
 * @author    Philip Bergman <pbergman@live.nl>
 * @copyright Philip Bergman
 */

namespace KeePassCli;

use \Symfony\Component\Console\Application as BaseApplication;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    public function __construct()
    {

        parent::__construct(ApplicationInfo::TITLE, ApplicationInfo::VERSION);

        /** add event dispatcher */
        $this->setDispatcher(new EventDispatcher());

        /** auto load commands */
        $this->loadCommands();

        /** add array helper */
        $this->getHelperSet()->set(new \KeePassCli\Helper\EntityTableHelper());

    }

    protected function getDefaultInputDefinition()
    {
        /** @var $inputDefinitions \Symfony\Component\Console\Input\InputDefinition */
        $inputDefinitions = parent::getDefaultInputDefinition();

        $inputDefinitions->addOptions(
            array(
                new InputOption('--reset-pwd', '-R', InputOption::VALUE_NONE, 'Will reset stored KeePass password'),

            )
        );
        return $inputDefinitions;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--reset-pwd', '-R'))) {
            $shm = new SHMHelper();
            $shm->remove();
            return 0;
        }

        return parent::doRun($input,$output);
    }

    /**
     * Reads commands folder and check if it is a instance from Symfony command,
     *
     * And if is instance from ValidateInterface than check is static function
     * returns true, so we can check for example package are installed
     *
     * @throws \Exception
     */
    protected function loadCommands()
    {

        $commandFolder = sprintf('%s/Commands',dirname(__FILE__));

        if(is_dir($commandFolder)){

            foreach (glob(sprintf("%s/*.php",$commandFolder)) as $filename) {

                $fileInfo           = pathinfo($filename);
                $className          = sprintf('KeePassCli\Commands\%s', $fileInfo['filename']);
                $baseCommand        = '\Symfony\Component\Console\Command\Command';
                $validateInterface  = 'KeePassCli\Commands\ValidateInterface';

                if(class_exists($className)){

                    $classRef = new \ReflectionClass($className);

                    if ($classRef->isSubclassOf($baseCommand)) {

                        $addClass = true;

                        if ($classRef->isSubclassOf($validateInterface)) {
                            /** @var $className \KeePassCli\Commands\ValidateInterface  */
                            $addClass = $className::validate();
                        }

                        if ( $addClass === true ) {
                            $this->add(new $className);
                        }
                    }
                }
            }

        }else{
            throw new \Exception(sprintf('Could not find folder %s', $commandFolder));
        }
    }

}