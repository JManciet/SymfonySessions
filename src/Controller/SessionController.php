<?php

namespace App\Controller;

use App\Entity\Module;
use App\Entity\Session;
use App\Entity\Programme;
use App\Entity\Stagiaire;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SessionController extends AbstractController
{
    /**
     * @Route("/session", name="app_session")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $sessions = $doctrine->getRepository(Session::class)->findBy([], ["nom" => "DESC"]);

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * @Route("/session/add", name="add_session")
     * @Route("/session/{id}/edit", name="edit_session")
     */
    public function add(ManagerRegistry $doctrine,  Session $session = null, Request $request): Response
    {
        if (!$session) {
            $session = new Session();
        }
        
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $session = $form->getData();
            $entityManager = $doctrine->getManager();
            //prepare
            $entityManager->persist($session);
            //insert into (execute)
            $entityManager->flush();

            return $this->redirectToRoute('app_session');
        }

        // vue pour ajouter le formulaire d'ajout
        return $this->render('session/add.html.twig', [
            'formAddSession' => $form->createView(),
            'edit' => $session->getId()
        ]);
    }


    /**
     * @Route("/session/{id}/delete", name="delete_session")
     */
    public function delete(ManagerRegistry $doctrine,  Session $session ): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($session);
        $entityManager->flush();

        return $this->redirectToRoute('app_session');
    }

    /**
     * @Route("/session/{id}", name="show_session")
     */
    public function show(Session $session, SessionRepository $sr): Response
    {

        $nonInscrits = $sr->findNonInscrits($session->getId());
        $nonProgrammes = $sr->findNonProgrammes($session->getId());

        return $this->render('session/show.html.twig', [
            'session' => $session,
            'nonInscrits' => $nonInscrits,
            'nonProgrammes' => $nonProgrammes,
        ]);
    }


    /**
     * @Route("/session/subscribe/{idSe}/{idSt}", name="subscribe_session")
     * @ParamConverter("session", options={"mapping": {"idSe": "id"}})
     * @ParamConverter("stagiaire", options={"mapping": {"idSt": "id"}})
     */
    public function subscribesession(ManagerRegistry $doctrine, Session $session, Stagiaire $stagiaire)
    {

        $em = $doctrine->getManager();
        $session->addStagiaire($stagiaire);
        $em->persist($session);
        $em->flush();

        return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
    }

    /**
     * @Route("/session/unsubscribe/{idSe}/{idSt}", name="unsubscribe_session")
     * @ParamConverter("session", options={"mapping": {"idSe": "id"}})
     * @ParamConverter("stagiaire", options={"mapping": {"idSt": "id"}})
     */
    public function unsubscribesession(ManagerRegistry $doctrine, Session $session, Stagiaire $stagiaire)
    {

        $em = $doctrine->getManager();
        $session->removeStagiaire($stagiaire);
        $em->persist($session);
        $em->flush();

        return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
    }

    /**
     * @Route("/session/program/{idSe}/{idMo}", name="program_module")
     * @ParamConverter("session", options={"mapping": {"idSe": "id"}})
     * @ParamConverter("module", options={"mapping": {"idMo": "id"}})
     */
    public function programModule(ManagerRegistry $doctrine, Session $session, Module $module)
    {

        $nbJours = $_POST['nbJours'];

        $programme = new Programme();

        $em = $doctrine->getManager();
        $programme->setNbJour($nbJours)->setModule($module);
        $em->persist($programme);
        $session->addProgramme($programme);
        $em->persist($session);
        $em->flush();

        return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
    }

    /**
     * @Route("/session/deprogram/{idPr}/{idSe}", name="deprogram_module")
     * @ParamConverter("session", options={"mapping": {"idSe": "id"}})
     * @ParamConverter("programme", options={"mapping": {"idPr": "id"}})
     */
    public function deprogramModule(ManagerRegistry $doctrine, Programme $programme, Session $session)
    {

        $em = $doctrine->getManager();
        $em->remove($programme);
        $em->flush();

        return $this->redirectToRoute('show_session', ['id' => $session->getId()]);
    }
}
