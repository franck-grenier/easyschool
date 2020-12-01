<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
        return $this->json($this->gradeRepo->getGlobalAverage(), Response::HTTP_OK);
    }
}
