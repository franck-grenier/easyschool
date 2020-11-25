<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Grade;
use App\Repository\StudentRepository;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
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
     * @Route("/students", name="index", methods={"GET"})
     * @Route("/students/{id}", name="student_by_id", methods={"GET"})
     */
    public function index(Request $request, SerializerInterface $serializer, String $id = null): Response
    {
        $id ?
            $data = $this->studentRepo->findOneBy(["id" => (int) $id]) :
            $data = $this->studentRepo->findAll();

        return $this->json($data, 200, [], ['groups' => 'student:read']);
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
     * @Route("/students/{id}", name="student_update", methods={"PUT", "PATCH"})
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
