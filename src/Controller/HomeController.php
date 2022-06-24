<?php

namespace App\Controller;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * @Route("/", name="home")
   */
  public function index(): Response
  {
    return $this->render('home.html.twig', [
      'rests' => $this->em->getRepository(Restaurant::class)->findBestRated(),
      'rating_on' => $_ENV['RATING_ON']
    ]);
  }
}
