<?php

namespace App\Repository;

use App\Entity\Grade;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Exception\NoGradesException;

/**
 * @method Grade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Grade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Grade[]    findAll()
 * @method Grade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    /**
     * returns the average grade of one student
     *
     * @param Student $student
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws NoGradesException
     */
    public function getStudentAverage(Student $student)
    {
        $average = $this->createQueryBuilder('g')
            ->select('avg(g.grade)')
            ->andWhere('g.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getSingleScalarResult();

        if (null === $average) {
            throw new NoGradesException($student);
        }

        return $average;
    }


    /**
     * returns global average of all grades known for all students
     *
     * @return mixed
     * @throws NoGradesException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws NoGradesException
     */
    public function getGlobalAverage()
    {
        $average = $this->createQueryBuilder('g')
            ->select('avg(g.grade)')
            ->getQuery()
            ->getSingleScalarResult();

        if (null === $average) {
            throw new NoGradesException();
        }

        return $average;
    }
}
