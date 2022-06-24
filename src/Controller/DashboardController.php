<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class DashboardController extends AbstractController
{
    /**
     * @IsGranted("ROLE_MEMBER")
     * @Route("/dashboard", name="dashboard")
     */
    public function index(): Response
    {
        return $this->render('dashboard.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
