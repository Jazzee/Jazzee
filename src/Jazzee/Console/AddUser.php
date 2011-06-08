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
                'uniqueName', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'Uniquename for the new users.  Generally this is their eduPersonPrinciplaName or some other federated authentication name.'
            )
        ))
        ->setHelp('Add a new user.  This will allow the user to log into the system, but until you add them to at least one role they will not be able to do anything.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      if(!$input->getOption('uniqueName')){
        $output->write('<error>--uniqueName is required.</error>' . PHP_EOL);
        exit(1);
      }
      $em = $this->getHelper('em')->getEntityManager();
      $user = new \Jazzee\Entity\User();
      $em->persist($user);
      $user->setUniqueName($input->getOption('uniqueName'));
      $em->flush();
    }
}