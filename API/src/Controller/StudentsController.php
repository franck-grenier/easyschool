<?php

namespace App\Controller;

use App\Entity\Student;
use App\Entity\Grade;
use App\Repository\StudentRepository;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\NoGradesException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use OpenApi\Annotations as OA;

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
     * Returns all students
     *
     * @Route("/students", name="students_list", methods={"GET"})

     * @OA\Tag(name="Students")
     */
    public function getAll(Request $request): Response
    {
        return $this->json($this->studentRepo->findAll(), Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * Returns one student from its identifier
     *
     * @todo utiliser l'autowiring pour injecter directement l'entity Student
     *       à partir de l'identifier sans avoir à faire appel au repo
     *
     * @Route("/students/{identifier}", name="student_by_identifier", methods={"GET"})
     * @OA\Tag(name="Students")
     */
    public function getOne(Request $request, String $identifier = null): Response
    {
        try {
            $student = $this->studentRepo->findOneByIdentifier($identifier);

            return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Create one new student
     *
     * @Route("/students", name="student_create", methods={"POST"})
     *
     * @OA\Tag(name="Students")
     */
    public function create(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        try {
            $data = $request->getContent();
            $student = $serializer->deserialize($data, Student::class, 'json');

            $errors = $validator->validate($student);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($student);
            $entityManager->flush();

            return $this->json($student, Response::HTTP_CREATED, [], ['groups' => 'student:create']);
        } catch (NotEncodableValueException $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Update student method handling both PATCH and PUT verbs on a single route, not "HTTP conventional"...
     * but very practical for a client
     *
     * @Route("/students/{identifier}", name="student_update", methods={"PATCH", "PUT"})
     *
     * @OA\Tag(name="Students")
     */
    public function update(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        String $identifier = null
    ): Response {
        try {
            $studentToUpdate = $this->studentRepo->findOneByIdentifier($identifier);
            $data = $request->getContent();
            $updatedStudent = $serializer->deserialize(
                $data,
                Student::class,
                'json',
                ['object_to_populate' => $studentToUpdate]
            );

            $errors = $validator->validate($updatedStudent);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($updatedStudent);
            $entityManager->flush();

            return $this->json($updatedStudent, Response::HTTP_OK, [], ['groups' => 'student:create']);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Removes a student
     *
     * @Route("/students/{identifier}", name="student_delete", methods={"DELETE"})
     *
     * @OA\Tag(name="Students")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, String $identifier = null): Response
    {
        try {
            $studentToDelete = $this->studentRepo->findOneByIdentifier($identifier);
            $entityManager->remove($studentToDelete);
            $entityManager->flush();

            return $this->json("", Response::HTTP_NO_CONTENT);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Add a grade to a student
     *
     * @Route("/students/{identifier}/grades", name="student_add_grade", methods={"POST"})
     *
     * @OA\Tag(name="Students")
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param String|null $identifier
     * @return Response
     */
    public function addGrade(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        String $identifier = null
    ): Response {
        try {
            $studentToGrade = $this->studentRepo->findOneByIdentifier($identifier);

            $data = $request->getContent();
            $grade = $serializer->deserialize(
                $data,
                Grade::class,
                'json'
            );

            $errors = $validator->validate($grade);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($grade);
            $grade->setStudent($studentToGrade);
            $entityManager->flush();

            return $this->json($grade, Response::HTTP_CREATED, [], ['groups' => 'grade:create']);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }


    /**
     * Returns the average grade of a student
     *
     * @Route("/students/{identifier}/grades/average", name="student_average_grade", methods={"GET"})
     *
     * @OA\Tag(name="Students")
     *
     * @param String $identifier
     */
    public function averageGrade(String $identifier): Response
    {
        try {
            $student = $this->studentRepo->findOneByIdentifier($identifier);
            $averageGrade = $this->gradeRepo->getStudentAverage($student);

            return $this->json($averageGrade, Response::HTTP_OK);
        } catch (NoGradesException $e) {
            return $this->json($e->getMessage(), Response::HTTP_OK);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }
}
