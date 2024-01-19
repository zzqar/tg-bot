<?php

namespace App\Logic\CallbackCommands;

use App\Attribute\Command;
use App\Game\LabyrinthGame;
use App\Interfaces\CallbackBotCommandInterface;

class LabyrinthBotCommand extends CallbackBotCommandInterface
{
    #[Command('lab_play')]
    public function play()
    {
       $response = (new LabyrinthGame())->play(
           $this->getParamByKey('target', 'не нашел'),
           $this->getParamByKey('x'),
           $this->getParamByKey('y')
       );
        $this->renderResponse($response);
    }
    #[Command('lab_lvl')]
    public function levelList()
    {
        $response = (new LabyrinthGame())->lvlList();
        $this->renderResponse($response);
    }
    #[Command('lab_back')]
    public function menu()
    {
        $response = (new LabyrinthGame())->menu();
        $this->renderResponse($response);
    }

    #[Command('lab_create')]
    public function createLevel(): void
    {
        $response = (new LabyrinthGame())->addLevel();
        $this->renderResponse($response);
    }
    #[Command('lab_stat')]
    public function statistic(): void
    {
        $response = (new LabyrinthGame())->statistic();
        $this->renderResponse($response);
    }

    /**
     * @throws \JsonException
     */
    #[Command('lab_clear')]
    public function clear(): void
    {
        $response = (new LabyrinthGame())->clear();
        $this->renderResponse($response);
    }

}
