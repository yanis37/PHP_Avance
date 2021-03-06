<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use ContainerMeCM7Uj\getForm_ChoiceListFactory_CachedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Film;
use App\Entity\UploadFile;
use Doctrine\Common\Persistence\ObjectManager;
use App\Services\VerifFilm;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


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
                $this->addFlash('success', 'Le film a bien ??t?? ajout?? !');
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

    /**
     * @Route("/import", name="import")
     */
    public function import(Request $request, EntityManagerInterface $manager)
    {

        $upload = new UploadFile();
        $form= $this->createFormBuilder($upload)
            ->add('name', FileType::class,[
                'label' => 'Choisissez un fichier',
            ])
            ->add('submit', SubmitType::class,[
                'label' => 'Valider',
                'attr'=>[
                    'style' => 'margin-top:1em;'
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $upload->getName();
            $fileName = md5(uniqid()).'.'.$file->getClientOriginalExtension();
            $file->move($this->getParameter('upload_directory'), $fileName);

            if($this->verifyExt($fileName)==true){
                foreach ($this->getDataFromFile($fileName) as $row){
                    $film = new Film();
                    $film
                        ->setName($row['name'])
                        ->setDescription($row['description'])
                        ->setNote($row['score'])
                        ->setVotersNumber(1)
                    ;
                    $manager->persist($film);
                }
                $manager->flush();
                $this->addFlash('success',"L'ajout a bien fonctionn??");
            }



        }
        return $this->render('blog/import.html.twig',[
            'importForm' => $form->createView(),
        ]);
    }

    private function getDataFromFile($fileName){

        $file = $this->getParameter('upload_directory').$fileName;
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $normalizer = [new ObjectNormalizer()];
        $encoder = [new CsvEncoder()];
        $serializer = new Serializer($normalizer, $encoder);
        $fileString = file_get_contents($file);
        $data = $serializer->decode($fileString,$fileExt);

        return $data;
    }

    private function verifyExt($fileName){

        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($fileExt!='csv'){
            $this->addFlash('error',"Erreur : le fichier n'est pas au format '.csv'.");
            return False;
        }
        return True;
    }
}
