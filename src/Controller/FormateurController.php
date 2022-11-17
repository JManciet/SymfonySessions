<?php

namespace App\Controller;

use App\Entity\Formateur;
use App\Form\FormateurType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormateurController extends AbstractController
{
    /**
     * @Route("/formateur", name="app_formateur")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $formateurs = $doctrine->getRepository(Formateur::class)->findBy([], ["nom" => "DESC"]);

        return $this->render('formateur/index.html.twig', [
            'formateurs' => $formateurs,
        ]);
    }


    /**
     * @Route("/formateur/add", name="add_formateur")
     * @Route("/formateur/{id}/edit", name="edit_formateur")
     */
    public function add(ManagerRegistry $doctrine,  Formateur $formateur = null, Request $request): Response
    {
        if (!$formateur) {
            $formateur = new Formateur();
        }
        
        $form = $this->createForm(FormateurType::class, $formateur);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $formateur = $form->getData();
            $entityManager = $doctrine->getManager();
            //prepare
            $entityManager->persist($formateur);
            //insert into (execute)
            $entityManager->flush();

            return $this->redirectToRoute('app_formateur');
        }

        // vue pour ajouter le formulaire d'ajout
        return $this->render('formateur/add.html.twig', [
            'formAddFormateur' => $form->createView(),
            'edit' => $formateur->getId()
        ]);
    }

    /**
     * @Route("/formateur/{id}/delete", name="delete_formateur")
     */
    public function delete(ManagerRegistry $doctrine,  Formateur $formateur ): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($formateur);
        $entityManager->flush();

        return $this->redirectToRoute('app_formateur');
    }    


    /**
     * @Route("/formateur/{id}", name="show_formateur")
     */
    public function show(Formateur $formateur): Response
    {
        return $this->render('formateur/show.html.twig', [
            'formateur' => $formateur
        ]);
    }
}
