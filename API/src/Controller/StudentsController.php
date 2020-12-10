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
use Nelmio\ApiDocBundle\Annotation\Model;

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
     *
     * @OA\Response(
     *     response=200,
     *     description="a JSON array of students",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Student::class, groups={"student:read"}))
     *     )
     * )
     * @OA\Tag(name="Students")
     */
    public function getAll(Request $request): Response
    {
        return $this->json($this->studentRepo->findAll(), Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * Returns one student and its grades from identifier
     *
     * @Route("/students/{identifier}", name="student_by_identifier", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="a JSON student object with its grades",
     *     @Model(type=Student::class, groups={"student:read"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="no student found with identifier")
     * )
     * @OA\Tag(name="Students")
     */
    public function getOne( Student $student ): Response
    {
        return $this->json($student, Response::HTTP_OK, [], ['groups' => 'student:read']);
    }

    /**
     * Create one new student
     *
     * @Route("/students", name="student_create", methods={"POST"})
     *
     * @OA\RequestBody(
     *     description="JSON object with mandatory student data",
     *     required=true,
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Student::class, groups={"student:write"})
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="the new student JSON object created",
     *     @Model(type=Student::class, groups={"student:created"})
     * )
     * @OA\Response(
     *     response=400,
     *     description="bad request, unable to create new student, something wrong with your request body")
     * )
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

            $errors = $validator->validate($student , null , ['groups' => 'student:write']);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($student);
            $entityManager->flush();

            return $this->json($student, Response::HTTP_CREATED, [], ['groups' => 'student:created']);
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
     * @Route("/students/{identifier}", name="student_update", methods={"PUT"})
     *
     * @OA\RequestBody(
     *     description="JSON object with student data (all of them) to update",
     *     required=true,
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Student::class, groups={"student:update"})
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="student updated",
     *     @Model(type=Student::class, groups={"student:created"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="no student found with identifier"
     * )
     * @OA\Response(
     *     response=400,
     *     description="bad request, unable to update student, something wrong with your request body"
     * )
     * @OA\Tag(name="Students")
     */
    public function update(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Student $studentToUpdate
    ): Response {
        try {
            $data = $request->getContent();
            $studentToUpdateData = $serializer->deserialize(
                $data,
                Student::class,
                'json'
            );

            $errors = $validator->validate($studentToUpdateData , null , ['groups' => 'student:update']);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            // not good to deserialize once again...
            // @todo find a way to update entity $studentToUpdate from already deserialized and validated $studentToUpdateData entity
            $serializer->deserialize(
                $data,
                Student::class,
                'json',
                ['object_to_populate' => $studentToUpdate]
            );

            $entityManager->persist($studentToUpdate);
            $entityManager->flush();

            return $this->json($studentToUpdate, Response::HTTP_OK, [], ['groups' => 'student:created']);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }

    /**
     * Removes a student
     *
     * @Route("/students/{identifier}", name="student_delete", methods={"DELETE"})
     *
     * @OA\Response(
     *     response=204,
     *     description="student deleted"
     * )
     * @OA\Response(
     *     response=404,
     *     description="no student found with identifier"
     * )
     * @OA\Tag(name="Students")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, Student $studentToDelete): Response
    {
        try {
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
     * @OA\RequestBody(
     *     description="JSON object with mandatory grade data",
     *     required=true,
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Grade::class, groups={"grade:write"})
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="student graded",
     *     @Model(type=Grade::class, groups={"grade:created"})
     * )
     * @OA\Response(
     *     response=404,
     *     description="no student found with identifier"
     * )
     * @OA\Response(
     *     response=400,
     *     description="bad request, unable to grade student, something wrong with your request body"
     * )
     * @OA\Tag(name="Students")
     */
    public function addGrade(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Student $studentToGrade
    ): Response {
        try {
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

            return $this->json($grade, Response::HTTP_CREATED, [], ['groups' => 'grade:created']);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }


    /**
     * Returns the average grade of a student
     *
     * @Route("/students/{identifier}/grades/average", name="student_average_grade", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="student's average grade",
     *     @OA\JsonContent(type="string", example="17.33")
     * )
     * @OA\Response(
     *     response=417,
     *     description="student has no grades yet, so no average available"
     * )
     * @OA\Response(
     *     response=404,
     *     description="no student found with identifier"
     * )
     * @OA\Tag(name="Students")
     */
    public function averageGrade(Student $student): Response
    {
        try {
            $averageGrade = $this->gradeRepo->getStudentAverage($student);
            return $this->json($averageGrade, Response::HTTP_OK);
        } catch (NoGradesException $e) {
            return $this->json($e->getMessage(), Response::HTTP_EXPECTATION_FAILED);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }
    }
}
