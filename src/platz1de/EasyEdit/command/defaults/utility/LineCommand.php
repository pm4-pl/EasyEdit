<?php

namespace platz1de\EasyEdit\command\defaults\utility;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\Messages;
use platz1de\EasyEdit\pattern\block\StaticBlock;
use platz1de\EasyEdit\pattern\parser\ParseError;
use platz1de\EasyEdit\task\editing\LineTask;
use platz1de\EasyEdit\task\editing\pathfinding\PathfindingTask;
use platz1de\EasyEdit\utils\BlockParser;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class LineCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/line", "Draw a line", [KnownPermissions::PERMISSION_EDIT, KnownPermissions::PERMISSION_GENERATE], "//line <x> <y> <z> [pattern]\n//line find <x> <y> <z> [pattern]\n//line solid <x> <y> <z> [pattern]");
	}

	/**
	 * @param Player   $player
	 * @param string[] $args
	 */
	public function process(Player $player, array $args): void
	{
		if (count($args) > 2) {
			if (count($args) > 3 && !is_numeric($args[0])) {
				$mode = array_shift($args);
			} else {
				$mode = "direct"; //TODO: use a better parser
			}

			$x = (int) $args[0];
			$y = (int) $args[1];
			$z = (int) $args[2];

			if (isset($args[3])) {
				try {
					$block = BlockParser::getBlock($args[3]);
				} catch (ParseError $exception) {
					$player->sendMessage($exception->getMessage());
					return;
				}
			} else {
				$block = VanillaBlocks::CONCRETE()->setColor(DyeColor::RED());
			}

			switch ($mode) {
				case "line":
				case "direct":
				default:
					LineTask::queue($player->getName(), $player->getWorld()->getFolderName(), $player->getPosition(), new Vector3($x, $y, $z), StaticBlock::fromBlock($block));
					break;
				case "find":
				case "search":
					PathfindingTask::queue($player->getName(), $player->getWorld()->getFolderName(), $player->getPosition(), new Vector3($x, $y, $z), true, StaticBlock::fromBlock($block));
					break;
				case "find-line":
				case "find-direct":
				case "no-diagonal":
				case "solid":
					PathfindingTask::queue($player->getName(), $player->getWorld()->getFolderName(), $player->getPosition(), new Vector3($x, $y, $z), false, StaticBlock::fromBlock($block));
					break;
			}
		} else {
			$player->sendMessage($this->getUsage());
		}
	}

	public function getCompactHelp(): string
	{
		return "//line <x> <y> <z> [pattern] - Draw a direct line to given position\n//line find <x> <y> <z> [pattern] - Find a valid path to the destination, " . Messages::RESOURCE_WARNING . "\n//line solid <x> <y> <z> [pattern] - Find a solid path to the destination, " . Messages::RESOURCE_WARNING;
	}
}