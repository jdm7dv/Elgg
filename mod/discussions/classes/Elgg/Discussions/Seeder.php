<?php

namespace Elgg\Discussions;

use Elgg\Database\Seeds\Seed;

/**
 * Add database seed
 *
 * @internal
 */
class Seeder extends Seed {

	private $status = [
		'open',
		'closed',
	];

	/**
	 * {@inheritdoc}
	 */
	public function seed() {
		$this->advance($this->getCount());

		while ($this->getCount() < $this->limit) {
			$metadata = [
				'status' => $this->getRandomStatus(),
				'excerpt' => $this->faker()->sentence(),
			];

			$attributes = [
				'subtype' => 'discussion',
				'container_guid' => $this->getRandomGroup()->guid,
			];

			$discussion = $this->createObject($attributes, $metadata);
			if (!$discussion) {
				continue;
			}

			$this->createComments($discussion);
			$this->createLikes($discussion);

			elgg_create_river_item([
				'action_type' => 'create',
				'subject_guid' => $discussion->owner_guid,
				'object_guid' => $discussion->guid,
				'target_guid' => $discussion->container_guid,
			]);

			$this->advance();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function unseed() {

		$discussions = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'discussion',
			'metadata_names' => '__faker',
			'limit' => 0,
			'batch' => true,
		]);

		/* @var $discussions \ElggBatch */

		$discussions->setIncrementOffset(false);

		foreach ($discussions as $discussion) {
			if ($discussion->delete()) {
				$this->log("Deleted discussion $discussion->guid");
			} else {
				$this->log("Failed to delete discussion $discussion->guid");
			}

			$this->advance();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getType() : string {
		return 'discussion';
	}

	/**
	 * Returns random discussion status
	 * @return string
	 */
	public function getRandomStatus() {
		$key = array_rand($this->status, 1);

		return $this->status[$key];
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getCountOptions() : array {
		return [
			'type' => 'object',
			'subtype' => 'discussion',
		];
	}
}
