<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TypeBatterieRepository;
use App\Entity\TypeBatterie;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ModifierTypeBatterieType;
use App\Form\SupprimerTypeBatterieType;
use Symfony\Component\HttpFoundation\Request;


class BaseController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [

        ]);
    }

    #[Route('/admin-liste-typebatterie', name: 'app_liste_type_batterie', methods: ['GET', 'POST'])]
    public function listeBatteries(Request $request, TypeBatterieRepository $TypeBatterieRepository,
        EntityManagerInterface $em): Response {
            $batteries = $TypeBatterieRepository->findAll();
        $form = $this->createForm(SupprimerTypeBatterieType::class, null, [
            'batteries' => $batteries,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedBatteries = $form->get('batteries')->getData();
            foreach ($selectedBatteries as $batterie) {
                $em->remove($batterie);
            }
            $em->flush();
            $this->addFlash('notice', 'Type batterie supprimées avec succès');
            return $this->redirectToRoute('app_liste_type_batterie');
        }
        return $this->render('base/liste-batterie.html.twig', [
            'batteries' => $batteries,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin-modifier-batterie/{id}', name: 'app_modifier_batterie')]
    public function modifierBatterieType(Request $request, TypeBatterie
         $batterie, EntityManagerInterface $em): Response {
        $form = $this->createForm(ModifierTypeBatterieType::class, $batterie);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($batterie);
                $em->flush();
                $this->addFlash('notice', 'Type Batterie modifiée');
                return $this->redirectToRoute('app_liste_type_batterie');
            }
        }
        return $this->render('base/modifier-batterie.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/admin-supprimer-batterie/{id}', name: 'app_supprimer_batterie')]
    public function supprimerBatterie(Request $request, TypeBatterie
         $batterie, EntityManagerInterface $em): Response {
        if ($batterie != null) {
            $em->remove($batterie);
            $em->flush();
            $this->addFlash('notice', 'Batterie supprimée');
        }
        return $this->redirectToRoute('app_liste_type_batterie');
    }

}
