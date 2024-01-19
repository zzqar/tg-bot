<?php

namespace App\Game;

use App\Exceptions\GameException;
use App\Helpers\GameResponse;
use Exception;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Throwable;

class LabyrinthGame
{
    protected const SIZE = 13;
    protected const LEVELS_MAX_COUNT = 5;

    protected const LVL_LIST_FILENAME = 'lvl_list.json';
    protected const START_CORD = 1;
    protected const FINISH_CORD = self::SIZE - 2;


    public function play(string $target, $x = null, $y = null)
    {

        try {
            $level = $this->getLvlByID($target);
            $text[] = '*Уровень:' . $level['name'] . '*';
            $text[] = str_repeat("-", 60);
            if ($x === null) {
                $playerCordX = self::START_CORD;
                $playerCordY = self::START_CORD;
            } else {
                $playerCordX = $x;
                $playerCordY = $y;
            }
            $maze = $level['maze'];
            if ((int)$playerCordX === self::FINISH_CORD && (int)$playerCordY === self::FINISH_CORD) {
                $text[] = 'YOU WON!!!';
                return new GameResponse(implode("\n", $text),  $this->backKeyboard());
            }

            if ($maze[$playerCordY][$playerCordX] === '#' && $x !== null) {
                $text[] = 'Вы проиграли';
                return new GameResponse(implode("\n", $text),  $this->backKeyboard());
            }

            $text[] = $this->renderMeze(
                $level['maze'],
                $playerCordX,
                $playerCordY
            );

            return new GameResponse(implode("\n", $text), $this->playKeyboard($target, $playerCordX,$playerCordY));
        } catch (GameException $e) {
            return new GameResponse($e->getMessage(), $this->backKeyboard());
        }
    }



    /**
     * @return GameResponse
     */
    public function lvlList(): GameResponse
    {
        try {
            $lvlList = $this->getLvlList();

            $text[] = '*Список уровней*';
            $text[] = str_repeat("-", 60);
            $keys = [];

            foreach ($lvlList as $key => $lvl) {
                $level = $key + 1;
                $text[] = "{$level}: {$lvl['name']}";
                $keys[] = ['text' => $level, 'callback_data' => "/lab_play -target={$lvl['id']}"];
            }

            return new GameResponse(
                implode("\n", $text),
                $this->backKeyboard($keys)
            );

        } catch (GameException $e) {
            return new GameResponse($e->getMessage(), $this->backKeyboard());
        }
    }

    /**
     * @return GameResponse
     */
    public function statistic(): GameResponse
    {
        return new GameResponse('Пока не реализованно', $this->backKeyboard());
    }

    public function addLevel(): GameResponse
    {
        try {
            $lvlList = $this->getLvlList();
        } catch (GameException $e) {
            $lvlList = [];
        }

        if (count($lvlList) === self::LEVELS_MAX_COUNT) {
            $deletedLvl = array_shift($lvlList);
        }

        try {
            $newLvl = $this->createNewLevel();
        } catch (Throwable $e) {
            return new GameResponse('что то сломалось при формировании лабиринта: ' . $e->getMessage(), $this->backKeyboard());
        }

        $lvlList[] = $newLvl;

        try {
            file_put_contents(
                self::LVL_LIST_FILENAME,
                json_encode($lvlList, JSON_THROW_ON_ERROR)
            );
        } catch (Throwable $e) {
            return new GameResponse('что то сломалось тут: ' . $e->getMessage(), $this->backKeyboard());
        }

        $text[] = '*Уровень успешно добавлен*';
        $text[] = "Новый уровен {$newLvl['name']}";
        if (isset($deletedLvl)) {
            $text[] = "Так как не хватило место - удален уровень {$deletedLvl['name']}";
        }
        return new GameResponse(implode("\n", $text), $this->backKeyboard());
    }

    /**
     * @return GameResponse
     */
    public function menu(): GameResponse
    {
        $text[] = '*Лабиринт*';
        $text[] = str_repeat("-", 60);
        $text[] = 'Используя стрелочки пройди лабиринт';
        return new GameResponse(implode("\n", $text), $this->menuKeyboard());
    }

    public function clear(): GameResponse
    {
        file_put_contents(
            self::LVL_LIST_FILENAME,
            json_encode([], JSON_THROW_ON_ERROR)
        );
        return new GameResponse('Очишен список', $this->backKeyboard());
    }

    protected function renderMeze(array $meze, $playerCordX, $playerCordY)
    {
        $meze[$playerCordY][$playerCordX] = '🔴';
        if ($playerCordY !== self::FINISH_CORD || $playerCordX !== self::FINISH_CORD) {
            $meze[self::FINISH_CORD][self::FINISH_CORD] = '🟩';
        }

        foreach ($meze as $row) {
            $text[] = implode('', $row);
        }
        $result = implode("\n", $text);
        return str_replace(['-', '#'], ["⬜", "⬛"], $result);

    }



    protected function menuKeyboard(): InlineKeyboardMarkup
    {
        return new InlineKeyboardMarkup([
            [
                ['text' => 'Выбрать уровень', 'callback_data' => '/lab_lvl']
            ],
            [
                ['text' => 'Создать уровень', 'callback_data' => '/lab_create'],
                ['text' => 'Удалить уровни', 'callback_data' => '/lab_clear']
            ],
            [
                ['text' => 'Статистика', 'callback_data' => '/lab_stat']
            ],

        ]);
    }
    protected function playKeyboard($target, $x, $y): InlineKeyboardMarkup
    {
        $left = $x - 1;
        $up = $y - 1;
        $down = $y + 1;
        $right = $x + 1;
        $keys[] = ['text' => "⬅", 'callback_data' => "/lab_play -target={$target} -x={$left} -y={$y}"];
        $keys[] = ['text' => "⬆", 'callback_data' => "/lab_play -target={$target} -x={$x} -y={$up}"];
        $keys[] = ['text' => "⬇", 'callback_data' => "/lab_play -target={$target} -x={$x} -y={$down}"];
        $keys[] = ['text' => "️➡", 'callback_data' => "/lab_play -target={$target} -x={$right} -y={$y}"];
        return $this->backKeyboard($keys);
    }

    protected function backKeyboard(array $addButton = null): InlineKeyboardMarkup
    {
        if (isset($addButton)) {
            $key[] = $addButton;
        }
        $key[] = [
            ['text' => 'Назад', 'callback_data' => '/lab_back']
        ];

        return new InlineKeyboardMarkup($key);
    }

    /**
     * @throws GameException
     */
    public function getLvlByID(int $id): array
    {
        $lvlList = $this->getLvlList();
        $lvlList = array_column($lvlList, null, 'id');

        if (!isset($lvlList[$id])) {
            throw new GameException('Уровень уже удален');
        }

        return $lvlList[$id];
    }

    /**
     * @throws Exception
     */
    public function createNewLevel(): array
    {
        $id = time();
        return [
            'id' => $id,
            'name' => 'level-' . $id,
            'maze' => $this->generateMaze()

        ];
    }

    /**
     * Возврашает список уровней
     *
     * @return array
     * @throws GameException
     */
    protected function getLvlList(): array
    {
        try {
            return json_decode(
                file_get_contents(self::LVL_LIST_FILENAME),
                true,
            );

        } catch (Throwable $exception) {
            throw new GameException('Не найден ни один уровень');
        }
    }


    /**
     * @throws Exception
     */
    protected function generateMaze(): array
    {
        $SIZE = self::SIZE;
        $maze = array_fill(0, $SIZE, array_fill(0, $SIZE, '#'));
        $wall_list = [];
        $cell_row = random_int(1, $SIZE - 2);
        $cell_col = random_int(1, $SIZE - 2);
        $maze[$cell_row][$cell_col] = '-';
        $this->add_walls($cell_row, $cell_col, $maze, $wall_list, $SIZE);

        while (count($wall_list) > 0) {

            $id = random_int(0, count($wall_list) - 1);
            [$wall_row, $wall_col] = $wall_list[$id][0];
            [$cell_row, $cell_col] = $wall_list[$id][1];
            array_splice($wall_list, $id, 1);

            if ($maze[$wall_row][$wall_col] !== '#') {
                continue;
            }
            if ($maze[$cell_row][$cell_col] === '-') {
                continue;
            }

            $maze[$wall_row][$wall_col] = '-';
            $maze[$cell_row][$cell_col] = '-';

            $this->add_walls($cell_row, $cell_col, $maze, $wall_list, $SIZE);
        }

        return $maze;
    }

    /**
     * @param $row
     * @param $col
     * @param $SIZE
     * @return bool
     */
    protected function in_maze($row, $col, $SIZE): bool
    {
        return $row > 0 && $row < $SIZE - 1 && $col > 0 && $col < $SIZE - 1;
    }

    /**
     * Add the neighboring walls of the cell (row, col) to the wall list
     *
     * @param $row
     * @param $col
     * @param $maze
     * @param $wall_list
     * @param $SIZE
     * @return void
     */
    protected function add_walls($row, $col, &$maze, &$wall_list, $SIZE): void
    {
        // It's a 4-connected grid maze
        $dir = [[0, 1], [1, 0], [0, -1], [-1, 0]];

        foreach ($dir as $d) {
            // Calculate the neighboring wall position and the cell position
            $wall_row = $row + $d[0];
            $wall_col = $col + $d[1];
            $cell_row = $wall_row + $d[0];
            $cell_col = $wall_col + $d[1];

            // Make sure the wall grid is in the range of the maze
            if (!$this->in_maze($wall_row, $wall_col, $SIZE) || !$this->in_maze($cell_row, $cell_col, $SIZE)) {
                continue;
            }

            // Add the wall and the neighboring cell to the list
            $wall_list[] = [[$wall_row, $wall_col], [$cell_row, $cell_col]];
        }
    }

}
