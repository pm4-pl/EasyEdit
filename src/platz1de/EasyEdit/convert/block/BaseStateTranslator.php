<?php

namespace platz1de\EasyEdit\convert\block;

use platz1de\EasyEdit\utils\BlockParser;
use platz1de\EasyEdit\utils\RepoManager;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\tag\Tag;
use UnexpectedValueException;

/**
 * Always results in the same block type
 */
abstract class BaseStateTranslator extends BlockStateTranslator
{
	/**
	 * @var string[]
	 */
	private array $removedStates;
	/**
	 * @var array<string, Tag>
	 */
	private array $addedStates = [];
	/**
	 * @var array<string, array<string, Tag>>
	 */
	private array $valueReplacements = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);

		$added = $data["state_additions"] ?? [];
		if (!is_array($added)) {
			throw new UnexpectedValueException("state_additions must be an array");
		}
		foreach ($added as $state => $value) {
			$this->addedStates[$state] = BlockParser::tagFromStringValue($value);
		}

		$removed = $data["state_removals"] ?? [];
		if (!is_array($removed)) {
			throw new UnexpectedValueException("state_removals must be an array");
		}
		$this->removedStates = $removed;

		$replace = $data["state_values"] ?? [];
		if (!is_array($replace)) {
			throw new UnexpectedValueException("state_values must be an array");
		}
		foreach ($replace as $stateName => $values) {
			$this->valueReplacements[$stateName] = [];
			foreach ($values as $value) {
				$this->valueReplacements[$stateName][(string) $value] = BlockParser::tagFromStringValue($value);
			}
		}
	}

	public function translate(BlockStateData $state): BlockStateData
	{
		$states = $state->getStates();
		foreach ($this->removedStates as $removedState) {
			unset($states[$removedState]);
		}
		foreach ($this->addedStates as $addedState => $addedValue) {
			$states[$addedState] = clone $addedValue;
		}
		foreach ($states as $stateName => $stateValue) {
			if (isset($this->valueReplacements[$stateName])) {
				$states[$stateName] = clone($this->valueReplacements[$stateName][BlockParser::tagToStringValue($stateValue)] ?? $stateValue);
			}
		}
		return new BlockStateData($state->getName(), $states, RepoManager::getVersion());
	}
}