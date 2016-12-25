<?php
/*
 * NOTICE:
 * This code has been slightly altered by the Mzax_Emarketing module to use old php namespaces.
 */
/**
 * ua-parser
 *
 * Copyright (c) 2011-2012 Dave Olsen, http://dmolsen.com
 *
 * Released under the MIT license
 */
#namespace UAParser\Command;

#use Symfony\Component\Console\Command\Command;
#use Symfony\Component\Console\Input\InputArgument;
#use Symfony\Component\Console\Input\InputInterface;
#use Symfony\Component\Console\Input\InputOption;
#use Symfony\Component\Console\Output\OutputInterface;
#use UAParser\Util\Converter;

class UAParser_Command_ConvertCommand extends Symfony_Component_Console_Command_Command
{
    /** @var string */
    private $resourceDirectory;

    /** @var string */
    private $defaultYamlFile;

    public function __construct($resourceDirectory, $defaultYamlFile)
    {
        $this->resourceDirectory = $resourceDirectory;
        $this->defaultYamlFile = $defaultYamlFile;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ua-parser:convert')
            ->setDescription('Converts an existing _regexes.yaml file to a _regexes.php file.')
            ->addArgument(
                'file',
                Symfony_Component_Console_Input_InputArgument::OPTIONAL,
                'Path to the _regexes.yaml file',
                $this->defaultYamlFile
            )
            ->addOption(
                'no-backup',
                null,
                Symfony_Component_Console_Input_InputOption::VALUE_NONE,
                'Do not backup the previously existing file'
            )
        ;
    }

    protected function execute(Symfony_Component_Console_Input_InputInterface $input, Symfony_Component_Console_Output_OutputInterface $output)
    {
        $this->getConverter()->convertFile($input->getArgument('file'), $input->getOption('no-backup'));
    }

    private function getConverter()
    {
        return new UAParser_Util_Converter($this->resourceDirectory);
    }
}
