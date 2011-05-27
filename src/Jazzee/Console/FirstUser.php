<?php
namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 */
class FirstUser extends \Symfony\Component\Console\Command\Command
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('new-user')
        ->setDescription('Create a new user')
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'email', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'Email address for the new user.  Will be required to log into the system for the first time.'
            ),new \Symfony\Component\Console\Input\InputOption(
                'first-name', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'User First Name'
            ),new \Symfony\Component\Console\Input\InputOption(
                'last-name', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                'User Last name'
            ),
            new \Symfony\Component\Console\Input\InputOption(
                'password', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Optionally provide a password if you do not want one generated for you.'
            )
        ))
        ->setHelp('Create the first users with full adminstrative privileges.');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $error = false;
      if(!$input->getOption('email')){
        $output->write('<error>--email is required.</error>' . PHP_EOL);
        $error = true;
      }
      if(!$input->getOption('first-name')){
        $output->write('<error>--first-name is required.</error>' . PHP_EOL);
        $error = true;
      }
      if(!$input->getOption('last-name')){
        $output->write('<error>--last-name is required.</error>' . PHP_EOL);
        $error = true;
      }
      if($error) exit(1);
      $em = $this->getHelper('em')->getEntityManager();
      $user = new \Jazzee\Entity\User();
      $em->persist($user);
      $user->setEmail($input->getOption('email'));
      $user->setFirstName($input->getOption('first-name'));
      $user->setLastName($input->getOption('last-name'));
      $password = $input->getOption('password');
      if(!$password){
        $password = substr(md5(rand() . uniqid(rand(), true) . $input->getOption('email')), rand(10,20), rand(8,12));
        $output->write("<info>Your password is - {$password} - you will need it to login</info>" . PHP_EOL);
      }
      $user->setPassword($password);
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $em->persist($role);
      $role->setName('Administrator');
      $arr = array(
        'manage_users' => array('index', 'edit', 'new', 'reset'),
        'manage_configuration' => array('index'),
        'manage_roles' => array('index', 'edit', 'new')     
      );
      foreach($arr as $controller => $actions){
        foreach($actions as $action){
          $ra = new \Jazzee\Entity\RoleAction();
          $ra->setController($controller);
          $ra->setAction($action);
          $ra->setRole($role);
          $em->persist($ra);
        }
      }
      $user->addRole($role);
      $em->flush();
    }
}