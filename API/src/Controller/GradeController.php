<?php

namespace App\Controller;

use App\Exception\NoGradesException;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class GradeController extends AbstractController
{
    private $gradeRepo;

    public function __construct(GradeRepository $gradeRepo)
    {
        $this->gradeRepo = $gradeRepo;
    }

    /**
     * Returns the global average grade of all students
     *
     * @Route("/grades/average", name="grade_average", methods={"GET"})
     *
     * @OA\Response(
     *     response=200,
     *     description="students global average grade",
     *     @OA\JsonContent(type="string", example="15.25")
     * )
     * @OA\Response(
     *     response=417,
     *     description="students have no grades yet, so no global average available"
     * )
     * @OA\Tag(name="Grades")
     */
    public function average(): Response
    {
        try {
            return $this->json($this->gradeRepo->getGlobalAverage(), Response::HTTP_OK);
        } catch (NoGradesException $e) {
            return $this->json($e->getMessage(), Response::HTTP_EXPECTATION_FAILED);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }

    }
}
