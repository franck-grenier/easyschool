<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Grade;
use App\Repository\StudentRepository;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StudentsController extends AbstractController
{
    private $studentRepo;
    private $gradeRepo;

    public function __construct(StudentRepository $studentRepo, GradeRepository $gradeRepo)
    {
        $this->studentRepo = $studentRepo;
        $this->gradeRepo = $gradeRepo;
    }

    /**
     * @Route("/students", name="students_list", methods={"GET"})
     */
    public function getAllStudents(Request $request): Response
    {
        return $this->json($this->studentRepo->findAll(), 200, [], ['groups' => 'student:read']);
    }

    /**
     * @todo utiliser l'autowiring pour injecter directement l'entity Student à partir de l'identifier sans avoir à faire appel au repo
     *
     * @Route("/students/{identifier}", name="student_by_identifier", methods={"GET"})
     */
    public function getOneStudent(Request $request, String $identifier = null): Response
    {
        if (null === $identifier) {
            return $this->json("", Response::HTTP_BAD_REQUEST);
        }

        $student = $this->studentRepo->findOneBy(array('identifier' => $identifier));
        if (null === $student) {
            return $this->json("", Response::HTTP_NOT_FOUND);
        }

        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * @Route("/students", name="student_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $data = $request->getContent();
        $student = $serializer->deserialize($data, Student::class, 'json');
        $entityManager->persist($student);
        $entityManager->flush();

        return $this->json($student, 201 , [] , ['groups' => 'student:create']);
    }

    /**
     * @Route("/students/{identifier}", name="student_update", methods={"PUT", "PATCH"})
     */
    public function update(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {

    }

    /**
     * @Route("/students/{id}", name="student_delete", methods={"DELETE"})
     */
    public function delete(Request $request): Response
    {

    }

}
