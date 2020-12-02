<?php

namespace App\Controller;

use App\Exception\NoGradesException;
use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class GradeController extends AbstractController
{
    private $gradeRepo;

    public function __construct(GradeRepository $gradeRepo)
    {
        $this->gradeRepo = $gradeRepo;
    }

    /**
     * @Route("/grades/average", name="grade_average", methods={"GET"})
     */
    public function average(): Response
    {
        try {
            return $this->json($this->gradeRepo->getGlobalAverage(), Response::HTTP_OK);
        } catch (NoGradesException $e) {
            return $this->json($e->getMessage(), Response::HTTP_OK);
        } catch (HttpException $e) {
            return $this->json($e->getMessage(), $e->getStatusCode());
        }

    }
}
