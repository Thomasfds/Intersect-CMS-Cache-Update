<?php

namespace App\Controller;

use App\Settings\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskCronController extends AbstractController
{
    /**
     * @Route("/update/taskCron", name="task_cron")
     */
    public function index(Api $api): Response
    {
        $cache = new FilesystemAdapter();
        $rankNiveau = $cache->getItem('game.rankNiveau');
        $playersList = $cache->getItem('game.playersList');

        /**
         * Met à jours le cache pour le classement
         */
        if ($rankNiveau->isHit() or !$rankNiveau->isHit()) {
            $getRank = $api->getRank(0);

            $total = $getRank['Total'];
            $total_page = floor($total / 25);
            $joueurs_rank = $getRank['Values'];
            $rank_list = [];

            for ($i = 0; $i <= $total_page; $i++) {

                $joueurs = $api->getRank($i)['Values'];

                foreach ($joueurs as $joueur) {
                    $user =   $api->getUser($joueur['UserId']);

                    if ($joueur['UserId'] == $user['Id'] && !$user['IsBanned']) {
                        $rank_list[] = [
                            'id' => $joueur['Id'],
                            'name' => $joueur['Name'],
                            'level' => $joueur['Level'],
                            'exp' => $joueur['Exp'],
                            'expNext' => $joueur['ExperienceToNextLevel'],
                        ];
                    }
                }
            }

            usort($rank_list, function ($a, $b) {
                return $b['level'] > $a['level'];
            });

            $cache->deleteItem('game.rankNiveau');
            $rankNiveau->set($rank_list);
            $cache->save($rankNiveau);
        }

        /**
         * Met à jour la liste des joueurs
         */

        if ($playersList->isHit() or !$playersList->isHit()) {
            $all_players = $api->getAllPlayers(0);
            $total = $all_players['Total'];
            $total_page = floor($total / 25);

            for ($i = 0; $i <= $total_page; $i++) {
                $joueurs = $api->getAllPlayers(0)['Values'];

                // Loop for fill $joueurs_liste empty array
                foreach ($joueurs as  $key => $joueur) {
                    $user =   $api->getUser($joueur['UserId']);
                    if ($joueur['UserId'] == $user['Id'] && !$user['IsBanned']) {
                        $joueurs_liste[] = $joueur;
                    }
                }
            }

            $cache->deleteItem('players_list');
            $playersList->set($joueurs_liste);
            $cache->save($playersList);
        }

        return new JsonResponse(['message' => 'Update completed.']);
    }
}
