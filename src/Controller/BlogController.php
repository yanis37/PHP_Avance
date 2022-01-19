<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use ContainerMeCM7Uj\getForm_ChoiceListFactory_CachedService;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Film;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home(FilmRepository $repo): Response
    {


        $films = $repo->findBy([], ['note' => 'DESC','name' => 'ASC']);


        return $this->render('blog/home.html.twig', [
            'controller_name' => 'BlogController',
            'films'=>$films

        ]);
    }

    /**
     * @Route("/add", name="ajout", methods={"GET","POST"})
     */
    public function ajout(Request $request, EntityManagerInterface $em): Response
    {
        /*
        $form = $this->createFormBuilder()
            ->add('titre')
            ->add('note')
            ->add('email')
            ->add('Ajouter', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $film = new Film;
            $film->setName(data['titre']);
            $film->setVotersNumber(data['vote']);

        }*/
        return $this->render('blog/ajout.html.twig', [
            'controller_name' => 'BlogController',
            /*'form'=>$form->createView()*/
        ]);
    }
    /**
     * @Route("/film", name="film")
     */
    public function film(): Response
    {

        return $this->render('blog/film.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }
}
