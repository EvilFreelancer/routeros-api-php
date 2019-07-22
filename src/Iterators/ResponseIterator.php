<?php
/**
 * Created by PhpStorm.
 * User: kousakananako
 * Date: 2019/05/30
 * Time: 2:07
 */

namespace RouterOS\Iterators;


/**
 * Class ResponseIterator
 * @package RouterOS\Iterators
 */
class ResponseIterator implements \Iterator, \ArrayAccess, \Countable {
	public $parsed = [];
	public $raw = [];
	public $current;
	public $length;
	public function __construct($raw) {
		$this->current = 0;
		// This RAW should't be an error
		$positions = array_keys($raw, '!re');
		$this->length = count($positions);
		$count     = count($raw);
		$result    = [];

		if (isset($positions[1])) {

			foreach ($positions as $key => $position) {
				// Get length of future block
				$length = isset($positions[$key + 1])
					? $positions[$key + 1] - $position + 1
					: $count - $position;

				// Convert array to simple items
				$item = array_slice($raw,$position,$length);

				// Save as result
				$result[] = $item;
			}

		} else {
			$result = [$raw];
		}

		$this->raw = $result;
	}
	public function next(){
		++$this->current;
	}
	public function current() {
		if (isset($this->parsed[$this->current])){
			return $this->parsed[$this->current];
		} elseif (isset($this->raw[$this->current])){
			return $this->parseResponse($this->raw[$this->current])[0];
		} else {
			return FALSE;
		}
	}
	public function key() {
		return $this->current;
	}
	public function valid() {
		return isset($this->raw[$this->current]);
	}
	public function count() {
		return count($this->raw);
	}
	public function rewind() {
		$this->current = 0;
	}
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->parsed[] = $value;
			throw new \RuntimeException('don\'t append to me It will cause a Bug sometime I PROMISE');
		} else {
			$this->parsed[$offset] = $value;
		}
	}
	public function offsetExists($offset) {
		return isset($this->raw[$offset]) && $this->raw[$offset] !== ['!re'];
	}
	public function offsetUnset($offset) {
		unset($this->parsed[$offset]);
		unset($this->raw[$offset]);
	}
	public function offsetGet($offset) {
		if (isset($this->parsed[$offset])){
			return $this->parsed[$offset];
		} elseif(isset($this->raw[$offset]) && $this->raw[$offset] !== NULL) {
			$f = $this->parseResponse($this->raw[$offset]);
			if ($f !==[]){
				$r = $this->parsed[$offset] = $f[0];
				return $r;
			}
		} else {
			return FALSE;
		}
	}
	public function flush(){
		$this->raw = [];
		$this->parsed = [];
	}
	private function parseResponse(array $response): array
	{
		$result = [];
		$i      = -1;
		$lines  = \count($response);
		foreach ($response as $key => $value) {
			switch ($value) {
				case '!re':
					$i++;
					break;
				case '!fatal':
					$result = $response;
					break 2;
				case '!trap':
				case '!done':
					// Check for =ret=, .tag and any other following messages
					for ($j = $key + 1; $j <= $lines; $j++) {
						// If we have lines after current one
						if (isset($response[$j])) {
							$this->pregResponse($response[$j], $matches);
							if (isset($matches[1][0], $matches[2][0])) {
								$result['after'][$matches[1][0]] = $matches[2][0];
							}
						}
					}
					break 2;
				default:
					$this->pregResponse($value, $matches);
					if (isset($matches[1][0], $matches[2][0])) {
						$result[$i][$matches[1][0]] = $matches[2][0];
					}
					break;
			}
		}
		return $result;
	}
	private function pregResponse(string $value, &$matches)
	{
		preg_match_all('/^[=|\.](.*)=(.*)/', $value, $matches);
	}
}
