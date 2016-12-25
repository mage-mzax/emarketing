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
#use Symfony\Component\Console\Output\OutputInterface;
#use UAParser\Parser;

class UAParser_Command_ParserCommand extends Symfony_Component_Console_Command_Command
{
    protected function configure()
    {
        $this
            ->setName('ua-parser:parse')
            ->setDescription('Parses a user agent string and dumps the results.')
            ->addArgument(
                'user-agent',
                null,
                Symfony_Component_Console_Input_InputArgument::REQUIRED,
                'User agent string to analyze'
            )
        ;
    }

    protected function execute(Symfony_Component_Console_Input_InputInterface $input, Symfony_Component_Console_Output_OutputInterface $output)
    {
        $result = UAParser_Parser::create()->parse($input->getArgument('user-agent'));

        $output->writeln(json_encode($result, JSON_PRETTY_PRINT));
    }
}
