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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function getAll(Request $request): Response
    {
        return $this->json($this->studentRepo->findAll(), 200, [], ['groups' => 'student:read']);
    }

    /**
     * @todo utiliser l'autowiring pour injecter directement l'entity Student
     *       à partir de l'identifier sans avoir à faire appel au repo
     *
     * @Route("/students/{identifier}", name="student_by_identifier", methods={"GET"})
     */
    public function getOne(Request $request, String $identifier = null): Response
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
        }
    }

    /**
     * @Route("/students/{identifier}", name="student_update", methods={"PATCH", "PUT"})
     */
    public function updateFull(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        String $identifier = null,
        ValidatorInterface $validator
    ): Response {
        try {
            $studentToUpdate = $this->studentRepo->findOneBy(array('identifier' => $identifier));
            if (null === $studentToUpdate) {
                return $this->json("", Response::HTTP_NOT_FOUND);
            }

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
        }
        catch (NotEncodableValueException $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/students/{identifier}", name="student_delete", methods={"DELETE"})
     */
    public function delete(Request $request, String $identifier = null, EntityManagerInterface $entityManager): Response
    {
        $studentToDelete = $this->studentRepo->findOneBy(array('identifier' => $identifier));
        if (null === $studentToDelete) {
            return $this->json("", Response::HTTP_NOT_FOUND);
        }

        try {
            $entityManager->remove($studentToDelete);
            $entityManager->flush();

            return $this->json("", Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
