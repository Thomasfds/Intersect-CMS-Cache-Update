<?php

namespace App\Controller;

use App\Settings\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;

class TaskCronController extends AbstractController
{
    /**
     * @Route("/update/taskCron", name="task_cron")
     */
    public function index(Api $api): Response
    {
        $cache = new FilesystemAdapter();
        $filesystem = new Filesystem();

        $allPlayers = $cache->getItem('players_list');
        $playersList = $cache->getItem('game.playersList');
        $rankNiveau = $cache->getItem('game.rankNiveau');

        $allPlayerNext = false;
        $clanListNext = false;
        $rankOutlawNext = false;
        $rankPvPNext = false;
        $rankLevelNext = false;

        if (!$filesystem->exists($this->getParameter('cache_json'))) {
            $filesystem->mkdir($this->getParameter('cache_json'), 0777);
        }

        // Cache all players, allplayerNext in true
        if ($allPlayers->isHit() or !$allPlayers->isHit()) {
            $all_players = $api->getAllPlayers(0);
            $total = $all_players['Total'];
            $total_page = floor($total / 100);

            for ($i = 0; $i <= $total_page; $i++) {
                $joueurs = $api->getRank($i)['Values'];

                // Loop for fill $joueurs_liste empty array
                foreach ($joueurs as  $key => $joueur) {

                    $joueurs_liste[] = $joueur;
                }
            }

            $cache->deleteItem('players_list');
            file_put_contents($this->getParameter('cache_json') . 'allPlayers.json', '{}');
            $allPlayers->set($joueurs_liste);
            $cache->save($allPlayers);

            fopen($this->getParameter('cache_json') . 'allPlayers.json', "wb");
            file_put_contents($this->getParameter('cache_json') . 'allPlayers.json', json_encode($allPlayers->get()));

            $allPlayerNext = true;
        }


        // Cache all players with necessary data, clanListNext in true
        if ($allPlayerNext && $allPlayers->isHit() or !$allPlayers->isHit()) {
            
            $joueurs_liste = [];


            foreach ($allPlayers->get() as $joueur) {
                if ($joueur['Level'] >= 1 && $joueur['Name'] != "Admin") {
                    $joueurs_liste[] = [
                        'id' => $joueur['Id'],
                        'name' => $joueur['Name'],
                        'level' => $joueur['Level'],
                        'exp' => $joueur['Exp'],
                        'expNext' => $joueur['ExperienceToNextLevel'],
                        'class' => $joueur['ClassName']
                    ];
                }
            }

            $cache->deleteItem('game.playersList');
            file_put_contents($this->getParameter('cache_json') . 'players_list.json', '{}');
            $playersList->set($joueurs_liste);
            $cache->save($playersList);
            fopen($this->getParameter('cache_json') . 'players_list.json', "wb");
            file_put_contents($this->getParameter('cache_json') . 'players_list.json', json_encode($playersList->get()));
        }

    
        if ($rankLevelNext && $rankNiveau->isHit() or !$rankNiveau->isHit()) {
            $getRank = $api->getRank(0);

            $total = $getRank['Total'];
            $total_page = floor($total / 100);
            $joueurs = $getRank['Values'];
            $joueurs_liste = [];

            for ($i = 0; $i <= $total_page; $i++) {

                $joueurs = $api->getRank($i)['Values'];

                foreach ($joueurs as $joueur) {
                    $joueurs_liste[] = [
                        'id' => $joueur['Id'],
                        'name' => $joueur['Name'],
                        'level' => $joueur['Level'],
                        'exp' => $joueur['Exp'],
                        'expNext' => $joueur['ExperienceToNextLevel'],
                        'class' => $joueur['ClassName']
                    ];
                }
            }

            usort($joueurs_liste, function ($a, $b) {
                return $b['level'] > $a['level'];
            });

            $cache->deleteItem('game.rankNiveau');
            file_put_contents($this->getParameter('cache_json') . 'lvl.json', '{}');
            $rankNiveau->set($joueurs_liste);
            $cache->save($rankNiveau);
            fopen($this->getParameter('cache_json') . 'lvl.json', "wb");
            file_put_contents($this->getParameter('cache_json') . 'lvl.json', json_encode($rankNiveau->get()));
            return new JsonResponse(['success' => true]);
        }
    }
}
