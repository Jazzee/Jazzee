<?php
namespace Jazzee\Console;

/**
 * Validate database information
 * Just extend the doctrine orm command for this
 *
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage console
 */
class Validate extends \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('validate')
        ->setDescription('Validate the current database schema')
        ->setHelp(<<<EOT
'Validate that the mapping files are correct and in sync with the database.'
EOT
        );
    }
}