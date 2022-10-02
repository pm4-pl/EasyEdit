<?php

namespace platz1de\EasyEdit\command\defaults\utility;

use platz1de\EasyEdit\command\EasyEditCommand;
use platz1de\EasyEdit\command\exception\PatternParseException;
use platz1de\EasyEdit\command\KnownPermissions;
use platz1de\EasyEdit\pattern\block\StaticBlock;
use platz1de\EasyEdit\pattern\parser\ParseError;
use platz1de\EasyEdit\session\Session;
use platz1de\EasyEdit\task\editing\LineTask;
use platz1de\EasyEdit\task\pathfinding\PathfindingTask;
use platz1de\EasyEdit\utils\ArgumentParser;
use platz1de\EasyEdit\utils\BlockParser;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;

class LineCommand extends EasyEditCommand
{
	public function __construct()
	{
		parent::__construct("/line", [KnownPermissions::PERMISSION_EDIT, KnownPermissions::PERMISSION_GENERATE]);
	}

	/**
	 * @param Session  $session
	 * @param string[] $args
	 */
	public function process(Session $session, array $args): void
	{
		ArgumentParser::requireArgumentCount($args, 3, $this);
		if (count($args) > 3 && !is_numeric($args[0])) {
			$mode = array_shift($args);
		} else {
			$mode = "direct"; //TODO: use a better parser
		}

		$target = ArgumentParser::parseCoordinates($session, $args[0], $args[1], $args[2]);

		if (isset($args[3])) {
			try {
				$block = BlockParser::parseBlockIdentifier($args[3]);
			} catch (ParseError $exception) {
				throw new PatternParseException($exception);
			}
		} else {
			$block = VanillaBlocks::CONCRETE()->setColor(DyeColor::RED())->getFullId();
		}

		switch ($mode) {
			case "line":
			case "direct":
			default:
				$session->runTask(new LineTask($session->asPlayer()->getPosition(), $target, new StaticBlock($block)));
				break;
			case "find":
			case "search":
				$session->runTask(new PathfindingTask($session->asPlayer()->getWorld()->getFolderName(), $session->asPlayer()->getPosition(), $target, true, new StaticBlock($block)));
				break;
			case "find-line":
			case "find-direct":
			case "no-diagonal":
			case "solid":
				$session->runTask(new PathfindingTask($session->asPlayer()->getWorld()->getFolderName(), $session->asPlayer()->getPosition(), $target, false, new StaticBlock($block)));
				break;
		}
	}
}