<?php

namespace App\Game\TicTac\Enum;

enum State
{
    case menu;
    case statistics;
    case search;
    case bot_menu;
    case bot_play;
    case pvp;
    case end;
    case stats_menu;
    case settings;
}
