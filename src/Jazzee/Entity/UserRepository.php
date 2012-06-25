<?php
namespace Jazzee\Entity;

/**
 * UserRepository
 * Special Repository methods for User to make searchign for special conditions easier
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * find all by name
   *
   * @param string $firstName
   * @param string $lastName
   *
   * @return Doctrine\Common\Collections\Collection \Jazzee\Entity\User
   */
  public function findByName($firstName, $lastName)
  {
    $query = $this->_em->createQuery('SELECT u FROM Jazzee\Entity\User u WHERE (u.firstName IS NULL OR u.firstName LIKE :firstName) AND (u.lastName IS NULL OR u.lastName LIKE :lastName) ORDER BY u.lastName, u.firstName');
    $query->setParameter('firstName', $firstName);
    $query->setParameter('lastName', $lastName);

    return $query->getResult();
  }

  /**
   * find all users in a program
   *
   * @param \Jazzee\Entity\Program $program
   *
   * @return Doctrine\Common\Collections\Collection \Jazzee\Entity\User
   */
  public function findByProgram($program)
  {
    $query = $this->_em->createQuery('SELECT u FROM Jazzee\Entity\User u JOIN u.roles r WHERE r.program = :programId AND u.isActive = true ORDER BY u.lastName, u.firstName');
    $query->setParameter('programId', $program->getId());

    return $query->getResult();
  }

}