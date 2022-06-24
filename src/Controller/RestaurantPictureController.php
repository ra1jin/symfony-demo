<?php

namespace App\Controller;

use App\Entity\RestaurantPicture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;

class RestaurantPictureController extends AbstractController
{
  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * @Route("/restaurant-picture/delete/{id}", name="restaurant-picture-delete")
   */
  public function delete(RestaurantPicture $restaurantPicture): Response
  {
    if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() != $restaurantPicture->getRestaurant()->getManager()) {
      $this->addFlash('error', 'Vous n\'avez pas les droits !');
      return $this->redirectToRoute('restaurant-my');
    }
  
    $filesystem = new Filesystem();
    $filesystem->remove('uploads/' . $restaurantPicture->getFile());

    $this->em->remove($restaurantPicture);
    $this->em->flush();

    return JsonResponse::fromJsonString('{ "success": true }');
  }
}