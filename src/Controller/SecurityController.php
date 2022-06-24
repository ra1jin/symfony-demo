<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\City;
use App\Validator\SecurityFormValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Doctrine\ORM\EntityManagerInterface;


class SecurityController extends AbstractController
{
  private $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  /**
   * @Route("/login", name="app_login")
   */
  public function login(AuthenticationUtils $authenticationUtils): Response
  {
    if ($this->getUser()) {
      return $this->redirectToRoute('home');
    }

    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();
    return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
  }

  /**
   * @Route("/subscribe", name="app_subscribe")
   */
  public function subscribe(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
  {
    if ($this->getUser()) {
      return $this->redirectToRoute('home');
    }

    if ($request->isMethod('POST')) {
      $data = $request->request->all();

      try { SecurityFormValidator::subscribe($data); }
      catch(\Exception $e) {
        return $this->render('security/subscribe.html.twig', [
          'errors' => $e->getErrorExceptions()
        ]);
      }

      $user = new User();
      $user->setEmail($data['email']);
      $user->setRoles(($data['role'] == 'MANAGER') ? ['ROLE_MEMBER', 'ROLE_MANAGER'] : ['ROLE_MEMBER', 'ROLE_CLIENT']);
      $user->setPassword($passwordEncoder->encodePassword($user, $data['password']));
      $user->setFirstname($data['firstname']);
      $user->setLastname($data['lastname']);

      $userFound = $this->em->getRepository(User::class)->findBy(array('email' => $data['email']));
      if ($userFound) {
        $this->addFlash('error', 'Utilisateur déjà enregistré');
        return $this->redirectToRoute('app_subscribe');
      }

      if ($data['password'] != $data['password_confirm']) {
        $this->addFlash('error', 'Les Mots de passes ne correspondent pas !');
        return $this->redirectToRoute('app_subscribe');
      }

      $city = $this->em->getRepository(City::class)->createIfNotExist(['name' => $data['cityname'], 'zipcode' => $data['zipcode']]);
      $user->setCity($city);

      $this->em->persist($user);
      $this->em->flush();

      return $this->redirectToRoute('app_login');
    }

    return $this->render('security/subscribe.html.twig');
  }

  /**
   * @Route("/logout", name="app_logout")
   */
  public function logout()
  {
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

  /**
   * @Route("/not-found", name="app_notfound")
   */
  public function notFound()
  {
    return $this->render('security/not-found.html.twig');
  }
}
