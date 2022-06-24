<?php

namespace App\Controller;

use \DateTime;
use App\Entity\Restaurant;
use App\Entity\Review;
use App\Validator\ReviewFormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class ReviewController extends AbstractController
{
  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * @IsGranted("ROLE_CLIENT")
   * @Route("/review/create/{restaurantId}", name="review-create")
   */
  public function create(Request $request, $restaurantId): Response
  {
    $restaurant = $this->em->getRepository(Restaurant::class)->find($restaurantId);
    if (!$restaurant) {
      return $this->redirectToRoute('app_notfound');
    }

    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { ReviewFormValidator::create($data); }
      catch (\Exception $e) {
        return $this->render('reviews/review-form.html.twig', [
          'restaurant' => $restaurant,
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $review = new Review();
      $review->setMessage($data['message']);
      $review->setRating($data['rating']);
      $review->setCreatedAt(new DateTime('now'));
      $review->setAuthor($this->getUser());
      $review->setRestaurant($restaurant);

      $this->em->persist($review);
      $this->em->flush();
  
      $this->addFlash('success', 'Félicitation, nouvelle review crée !');
      return $this->redirectToRoute('restaurant-view', ['id' => $restaurant->getId()]);
    }

    return $this->render('reviews/review-form.html.twig', [
      'restaurant' => $restaurant
    ]);
  }

  /**
   * @IsGranted("ROLE_CLIENT")
   * @Route("/review/edit/{id}", name="review-edit")
   */
  public function edit(Request $request, Review $review): Response
  {
    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { ReviewFormValidator::create($data); }
      catch (\Exception $e) {
        return $this->render('reviews/review-form.html.twig', [
          'restaurant' => $review->getRestaurant(),
          'review' => $review,
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $review->setMessage($data['message']);
      $review->setRating($data['rating']);

      $this->em->persist($review);
      $this->em->flush();

      $this->addFlash('success', 'Félicitation, review éditée !');
      return $this->redirectToRoute('restaurant-view', ['id' => $review->getRestaurant()->getId()]);
    }

    return $this->render('reviews/review-form.html.twig', [
      'restaurant' => $review->getRestaurant(),
      'review' => $review
    ]);
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/review/create-response/{id}", name="review-create-response")
   */
  public function createResponse(Request $request, Review $parent): Response
  {
    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { ReviewFormValidator::response($data); }
      catch (\Exception $e) {
        dd($e);
        return $this->render('reviews/review-form.html.twig', [
          'parent' => $parent,
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $response = new Review();
      $response->setMessage($data['message']);
      $response->setRating(-1);
      $response->setCreatedAt(new DateTime('now'));
      $response->setAuthor($this->getUser());
      $response->setRestaurant($parent->getRestaurant());
      $response->setParent($parent);

      $this->em->persist($response);
      $this->em->flush();
  
      $this->addFlash('success', 'Félicitation, vous avez repondu à un avis !');
      return $this->redirectToRoute('restaurant-view', ['id' => $parent->getRestaurant()->getId()]);
    }

    return $this->render('reviews/review-response-form.html.twig', [
      'parent' => $parent
    ]);
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/review/edit-response/{id}", name="review-edit-response")
   */
  public function editResponse(Request $request, Review $response): Response
  {
    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { ReviewFormValidator::response($data); }
      catch (\Exception $e) {
        dd($e);
        return $this->render('reviews/review-form.html.twig', [
          'response' => $response,
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $response->setMessage($data['message']);

      $this->em->persist($response);
      $this->em->flush();
  
      $this->addFlash('success', 'Félicitation, vous avez modifier votre reponse !');
      return $this->redirectToRoute('restaurant-view', ['id' => $response->getRestaurant()->getId()]);
    }

    return $this->render('reviews/review-response-form.html.twig', [
      'parent' => $response->getParent(),
      'response' => $response
    ]);
  }
}
