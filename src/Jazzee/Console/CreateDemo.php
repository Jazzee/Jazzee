<?php
namespace Jazzee\Console;

/**
 * Create a demo application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class CreateDemo extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('create-demo')->setDescription('Create a demo application.');
    $this->addOption('programName', 'p', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Program Name');
    $this->addOption('shortName', 's', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Short Name');
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $jazzeeConfiguration = new \Jazzee\Configuration;
    if ($jazzeeConfiguration->getStatus() == 'PRODUCTION') {
      $output->write('<error>You cannot create a demo in production.</error>' . PHP_EOL);
      exit();
    }

    $programName = $input->getOption('programName') ? $input->getOption('programName') : 'Demo';
    $shortName = $input->getOption('shortName') ? $input->getOption('shortName') : 'demo';
    $entityManager = $this->getHelper('em')->getEntityManager();
    if ($entityManager->getRepository('\Jazzee\Entity\Program')->findBy(array('name' => $programName))) {
      $output->write("<error>A program named '{$programName}' already exists.</error>" . PHP_EOL);
      exit();
    }
    if ($entityManager->getRepository('\Jazzee\Entity\Program')->findBy(array('shortName' => $shortName))) {
      $output->write("<error>A program with the shortname '{$shortName}' already exists.</error>" . PHP_EOL);
      exit();
    }
    if (!$cycle = $entityManager->getRepository('\Jazzee\Entity\Cycle')->findOneBy(array('name' => 'demo'))) {
      $cycle = new \Jazzee\Entity\Cycle;
      $cycle->setName('demo');
      $cycle->setStart('today');
      $cycle->setEnd('next year');
      $entityManager->persist($cycle);
    }

    $program = new \Jazzee\Entity\Program;
    $program->setName($programName);
    $program->setShortName($shortName);
    $entityManager->persist($program);

    $application = new \Jazzee\Entity\Application;
    $application->setProgram($program);
    $application->setCycle($cycle);
    $application->setWelcome('THIS IS A DEMO APP.');
    $application->visible();
    $application->setOpen('yesterday');
    $application->setClose('next year');
    $application->publish(true);
    $entityManager->persist($application);

    $StandardPageType = $entityManager->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class' => '\Jazzee\Page\Standard'));

    $page = new \Jazzee\Entity\Page;
    $page->setTitle('Optional Page');
    $page->optional();
    $page->setType($StandardPageType);
    $entityManager->persist($page);

    $count = 1;
    foreach ($entityManager->getRepository('\Jazzee\Entity\ElementType')->findAll() as $type) {
      $element = $this->demoElement($type, $entityManager);
      $element->setTitle($type->getName());
      $element->optional();
      $element->setWeight($count);
      $page->addElement($element);
      $entityManager->persist($element);
      $count++;
    };

    $applicationPage = new \Jazzee\Entity\ApplicationPage;
    $applicationPage->setApplication($application);
    $applicationPage->setPage($page);
    $applicationPage->setKind(\Jazzee\Entity\ApplicationPage::APPLICATION);
    $applicationPage->setWeight(1);
    $entityManager->persist($applicationPage);


    $page = new \Jazzee\Entity\Page;
    $page->setTitle('Required Page');
    $page->setType($StandardPageType);
    $entityManager->persist($page);

    $count = 1;
    foreach ($entityManager->getRepository('\Jazzee\Entity\ElementType')->findAll() as $type) {
      $element = $this->demoElement($type, $entityManager);
      $element->setTitle($type->getName());
      $element->required();
      $element->setWeight($count);
      $page->addElement($element);
      $entityManager->persist($element);
      $count++;
    };

    $applicationPage = new \Jazzee\Entity\ApplicationPage;
    $applicationPage->setApplication($application);
    $applicationPage->setPage($page);
    $applicationPage->setKind(\Jazzee\Entity\ApplicationPage::APPLICATION);
    $applicationPage->setWeight(2);
    $entityManager->persist($applicationPage);

    $entityManager->flush();
    $output->write("<info>Demo program {$programName} created successfully.</info>" . PHP_EOL);
  }

  /**
   * Create a demo element
   * @param \Jazzee\Entity\ElementType $type
   * $param \Doctrine\ORM\EntityManager $entityManager
   * @return \Jazzee\Entity\Element
   */
  protected function demoElement(\Jazzee\Entity\ElementType $type, \Doctrine\ORM\EntityManager $entityManager)
  {
    $element = new \Jazzee\Entity\Element;
    $element->setType($type);
    switch ($type->getClass()) {
      case '\Jazzee\Element\CheckboxList':
      case '\Jazzee\Element\RadioList':
      case '\Jazzee\Element\SelectList':
        for ($i = 1; $i <= rand(5, 15); $i++) {
          $item = new \Jazzee\Entity\ElementListItem;
          $item->setWeight($i);
          $item->activate();
          $item->setValue(\Foundation\Utility::ordinalValue($i) . ' item');
          $entityManager->persist($item);
          $element->addItem($item);
        }
        break;
      case '\Jazzee\Element\RankingList':
        for ($i = 1; $i <= rand(5, 15); $i++) {
          $item = new \Jazzee\Entity\ElementListItem;
          $item->setWeight($i);
          $item->activate();
          $item->setValue(\Foundation\Utility::ordinalValue($i) . ' item');
          $entityManager->persist($item);
          $element->addItem($item);
        }
        $element->setMin(1);
        $element->setMax(4);
        break;
      case '\Jazzee\Element\Textarea':
        $element->setMax(500);
        break;
    }

    return $element;
  }

}