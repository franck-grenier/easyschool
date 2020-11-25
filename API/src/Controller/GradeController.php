<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GradeController extends AbstractController
{
    /**
     * @Route("/grade", name="grade")
     */
    public function index(): Response
    {
        return $this->render('grade/index.html.twig', [
            'controller_name' => 'GradeController',
        ]);
    }
}
