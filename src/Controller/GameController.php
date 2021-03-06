<?php

namespace App\Controller;

use App\Settings\Api;
use App\Settings\CmsSettings;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Knp\Component\Pager\PaginatorInterface; // Nous appelons le bundle KNP Paginator
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class GameController extends AbstractController
{
    /**
     * @Route("/players", name="game.players.liste",  requirements={"_locale": "en|fr"})
     */
    public function listeJoueurs(Api $api, $page = 0, PaginatorInterface $paginator, Request $request, CmsSettings $settings): Response
    {
        $serveur_statut = $api->ServeurStatut();

        if ($serveur_statut['success']) {

            $joueurs = $settings->getCache('game.playersList');

            $joueurs_liste = [];

            foreach ($joueurs as $joueur) {

                if ($joueur['Level'] >= 1 && $joueur['Name'] != "Admin") {
                    $joueurs_liste[] = ['user' => $joueur['UserId'], 'name' => $joueur['Name'], 'level' => $joueur['Level'], 'exp' => $joueur['Exp'], 'expNext' => $joueur['ExperienceToNextLevel']];
                }
            }


            $joueurs = $paginator->paginate(
                $joueurs_liste, // Requête contenant les données à paginer (ici nos articles)
                $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
                10 // Nombre de résultats par page
            );

            $response = new Response($this->renderView('game/players.html.twig', [
                'joueurs' => $joueurs,
            ]));

            $response->setPublic();
            $response->setSharedMaxAge(3600);
            // $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

            return $response;
        } else {
            return $this->render('game/players.html.twig', [
                'serveur_statut' => false
            ]);
        }
    }

    /**
     * @Route("/online-players", name="game.players.liste.online",  requirements={"_locale": "en|fr"})
     */
    public function listeJoueursEnLigne(Api $api, $page = 0): Response
    {
        $serveur_statut = $api->ServeurStatut();
        if ($serveur_statut['success']) {
            $joueurs = $api->onlinePlayers();

            $joueurs_liste = [];

            foreach ($joueurs as $joueur) {
                $joueurs_liste[] = ['name' => $joueur['Name'], 'level' => $joueur['Level'], 'exp' => $joueur['Exp'], 'expNext' => $joueur['ExperienceToNextLevel']];
            }

            $response = new Response($this->renderView('game/online.html.twig', [
                'joueurs' => $joueurs_liste,
            ]));

            $response->setPublic();
            $response->setSharedMaxAge(60);
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

            return $response;

            return $this->render('game/online.html.twig', [
                'joueurs' => $joueurs_liste,
            ]);
        } else {
            return $this->render('game/online.html.twig', [
                'serveur_statut' => false
            ]);
        }
    }

    /**
     * @Route("/rank/level", name="game.rank.level",  requirements={"_locale": "en|fr"})
     */
    public function rankNiveau(Api $api, CmsSettings $settings): Response
    {
        $serveur_statut = $api->ServeurStatut();
        if ($serveur_statut['success']) {

            $response = new Response($this->renderView('game/level_rank.html.twig', [
                'joueurs' => $settings->getCache('game.rankNiveau'),
            ]));

            $response->setPublic();
            $response->setSharedMaxAge(3600);
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');

            return $response;
        } else {
            return $this->render('game/level_rank.html.twig', [
                'serveur_statut' => false
            ]);
        }
    }
}
