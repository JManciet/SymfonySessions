<?php

namespace App\Controller;

use App\Entity\Session;
use App\Repository\SessionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Route("/session/{id}", name="show_session")
     */
    public function show(Session $session, SessionRepository $sr): Response
    {

        $nonInscrits = $sr->findNonInscrits($session->getId());

        return $this->render('session/show.html.twig', [
            'session' => $session,
            'nonInscrits' => $nonInscrits
        ]);
    }
}
