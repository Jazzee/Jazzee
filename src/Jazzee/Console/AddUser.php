<?php
namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 */
class AddUser extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('add-user')
        ->setDescription('Add a new user.')
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'eduPersonPrincipalName', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'The SAML eduPersonPrincipalName for the new user.'
            )
        ))
        ->setHelp('Add a new user using thier eduPersonPrincipalName.  This will allow the user to log into the system, but until you add them to at least one role they will not be able to do anything.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      if(!$input->getOption('eduPersonPrincipalName')){
        $output->write('<error>--eduPersonPrincipalName is required.</error>' . PHP_EOL);
        exit(1);
      }
      $em = $this->getHelper('em')->getEntityManager();
      $user = new \Jazzee\Entity\User();
      $em->persist($user);
      $user->setEduPersonPrincipalName($input->getOption('eduPersonPrincipalName'));
      $em->flush();
    }
}