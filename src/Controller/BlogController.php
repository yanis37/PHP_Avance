<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use ContainerMeCM7Uj\getForm_ChoiceListFactory_CachedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Film;
use Doctrine\Common\Persistence\ObjectManager;
use App\Services\VerifFilm;


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
    public function ajout(Request $request, EntityManagerInterface $manager): Response
    {
        $film = new Film();

        $form = $this->createFormBuilder($film)
            ->add('name')
            ->add('note')
            ->add('votersNumber')
            ->add('Ajouter', SubmitType::class, [
                'attr'=>[
                    'style' => 'margin-top:1em;'
                ]
            ])
            ->getForm();

        $omdbapi = new VerifFilm("16ced50c");

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if($omdbapi->exist($film->getName())=="True") {
                $film->setDescription($omdbapi->getDesc($film->getName()));
                $manager->persist($film);
                $manager->flush();
                $this->addFlash('success', 'Le film a bien été ajouté !');
            } else {
                $this->addFlash('error', 'Le film est introuvable.');
            }
        }

        return $this->render('blog/ajout.html.twig', [
            'controller_name' => 'BlogController',
            'form'=>$form->createView(),
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
