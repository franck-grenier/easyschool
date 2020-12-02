<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * Custom method to get one student from its identifier, used often
     * throws a NotFoundHttpException to have a clean error handling in controllers
     *
     * @todo We could discuss that a repository should not throw HTTP exceptions but I think it's fine for the exercise.
     *
     * @param String|null $identifier
     * @return Student|null
     * @throws NotFoundHttpException|BadRequestHttpException
     */
    public function findOneByIdentifier(String $identifier = null)
    {
        // @todo normalize "identifier" format (ie. UUID) and check this format here
        if (null === $identifier) {
            throw new BadRequestHttpException("No (or bad) identifier given");
        }
        
        $student = $this->findOneBy(array('identifier' => $identifier));

        if (null === $student) {
            throw new NotFoundHttpException(sprintf("No student with identifier %s", $identifier));
        }

        return $student;
    }
}
