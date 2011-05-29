<?php
namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 */
class UserRole extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('user-role')
        ->setDescription('Add a new user.')
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'eduPersonPrincipalName', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'The SAML eduPersonPrincipalName for the new user.'
            ),
            new \Symfony\Component\Console\Input\InputOption(
                'role', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'The name of the role.'
            )
        ))
        ->setHelp('Add a new user using thier eduPersonPrincipalName.  This will allow the user to log into the system, but until you add them to at least one role they will not be able to do anything.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $error = false;
      if(!$input->getOption('eduPersonPrincipalName')){
        $output->write('<error>--eduPersonPrincipalName is required.</error>' . PHP_EOL);
        $error = true;
      }
      if(!$input->getOption('role')){
        $output->write('<error>--role is required.</error>' . PHP_EOL);
        $error = true;
      }
      if($error) exit(1);
      $em = $this->getHelper('em')->getEntityManager();
      $user = $em->getRepository('\Jazzee\Entity\User')->findOneByEduPersonPrincipalName($input->getOption('eduPersonPrincipalName'));
      if(!$user){
        $output->write('<error>Bad user name.</error>' . PHP_EOL);
        exit();
      }
      
      $role = $em->getRepository('\Jazzee\Entity\Role')->findOneByName($input->getOption('role'));
      if(!$role){
        $output->write('<error>Bad role name.</error>' . PHP_EOL);
        exit();
      }
      $user->addRole($role);
      $em->persist($user);
      $em->flush();
    }
}