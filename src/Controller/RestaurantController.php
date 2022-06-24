<?php

namespace App\Controller;

use \DateTime;
use App\Entity\City;

use App\Entity\Restaurant;
use App\Entity\RestaurantPicture;
use App\Validator\RestaurantFormValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class RestaurantController extends AbstractController
{
  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/restaurant/create", name="restaurant-create")
   */
  public function create(Request $request): Response
  {
    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { RestaurantFormValidator::create($data); }
      catch(\Exception $e) {
        return $this->render('restaurants/restaurant-form.html.twig', ['errors' => $e->getErrorExceptions()]);
      }

      $restaurant = new Restaurant();
      $restaurant->setName($data['name']);
      $restaurant->setDescription($data['description']);
      $restaurant->setCreatedAt(new DateTime('now'));
      $restaurant->setManager($this->getUser());

      $city = $this->em->getRepository(City::class)->createIfNotExist(['name' => $data['cityname'], 'zipcode' => $data['zipcode']]);
      $restaurant->setCity($city);

      foreach ($request->files->get('pictures') as $image) {
        $filename = md5(uniqid()) . '.' . $image->getClientOriginalExtension();
        $image->move('uploads', $filename);

        $restaurantPic = new RestaurantPicture();
        $restaurantPic->setName($image->getClientOriginalName());
        $restaurantPic->setFile($filename);
        $this->em->persist($restaurantPic);
        $restaurant->addRestaurantPicture($restaurantPic);
      }

      $this->em->persist($restaurant);
      $this->em->flush();

      $this->addFlash('success', 'Félicitation, nouveau restaurant créer !');
      return $this->redirectToRoute('dashboard');
    }
  
    return $this->render('restaurants/restaurant-form.html.twig');
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/restaurant/edit/{id}", name="restaurant-edit")
   */
  public function edit(Restaurant $restaurant, Request $request): Response
  {
    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { RestaurantFormValidator::create($data); }
      catch(\Exception $e) {
        return $this->render('restaurants/restaurant-form.html.twig', [
          'restaurant' => $restaurant,
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $restaurant->setName($data['name']);
      $restaurant->setDescription($data['description']);
      $restaurant->setCreatedAt(new DateTime('now'));
      $restaurant->setManager($this->getUser());

      $cityFound = $this->em->getRepository(City::class)->findOneBy(array(
        'name' => $data['cityname'],
        'zipcode' => $data['zipcode']
      ));

      if($cityFound) {
        $restaurant->setCity($cityFound);
      }
      else {
        $city = new City();
        $city->setName($data['cityname']);
        $city->setZipcode($data['zipcode']);
        $this->em->persist($city);
        $restaurant->setCity($city);
      }

      foreach ($request->files->get('pictures') as $image) {
        $filename = md5(uniqid()) . '.' . $image->getClientOriginalExtension();
        $image->move('uploads', $filename);

        $restaurantPic = new RestaurantPicture;
        $restaurantPic->setName($image->getClientOriginalName());
        $restaurantPic->setFile($filename);
        $this->em->persist($restaurantPic);
        $restaurant->addRestaurantPicture($restaurantPic);
      }

      $this->em->persist($restaurant);
      $this->em->flush();

      $this->addFlash('success', 'Félicitation, nouveau restaurant créer !');
      return $this->redirectToRoute('dashboard');
    }
  
    return $this->render('restaurants/restaurant-form.html.twig', [
      'restaurant' => $restaurant
    ]);
  }

  /**
   * @Route("/restaurant/search", name="restaurant-search")
   */
  public function search(Request $request): Response
  {
    return $this->render('restaurants/restaurant-list.html.twig', [
      'title' => 'Resultat',
      'restaurants' => $this->em->getRepository(Restaurant::class)->findBySearch(array(
        'zipcode' => $request->query->get('zipcode')
      ))
    ]);
  }

  /**
   * @Route("/restaurant/list", name="restaurant-list")
   */
  public function list(): Response
  {
    return $this->render('restaurants/restaurant-list.html.twig', [
      'title' => 'Liste des restaurants',
      'restaurants' => $this->em->getRepository(Restaurant::class)->findAll()
    ]);
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/restaurant/my", name="restaurant-my")
   */
  public function my(): Response
  {
    return $this->render('restaurants/restaurant-list.html.twig', [
      'title' => 'Mes restaurants',
      'restaurants' => $this->em->getRepository(Restaurant::class)->findBy(array(
        'manager' => $this->getUser()
      ))
    ]);
  }

  /**
   * @Route("/restaurant/view/{id}", name="restaurant-view")
   */
  public function view(Restaurant $restaurant): Response
  {
    return $this->render('restaurants/restaurant-view.html.twig', [
      'restaurant' => $restaurant
    ]);
  }

  /**
   * @IsGranted("ROLE_MANAGER")
   * @Route("/restaurant/delete/{id}", name="restaurant-delete")
   */
  public function delete(Restaurant $restaurant): Response
  {
    if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() != $restaurant->getManager()) {
      $this->addFlash('error', 'Vous n\'avez pas les droits !');
      return $this->redirectToRoute('restaurant-my');
    }

    $this->em->remove($restaurant);
    $this->em->flush();

    $this->addFlash('success', 'Restaurant supprimé !');
    return $this->redirectToRoute('restaurant-my');
  }
}