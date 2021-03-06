<?php
require_once(__DIR__ . "/AWSFileManager.php");
require_once(__DIR__ . "/StatManager.php");

class BuildObject {
	public $id;
	public $blid;
	public $name;
	public $bricks;
	public $description;
	public $filename;
	public $url;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->blid = intval($resource->blid);
		$this->name = $resource->name;
		$this->bricks = intval($resource->bricks);
		$this->description = $resource->description;
		$this->filename = $resource->filename;
		$this->url = "https://s3.amazonaws.com/" . urlencode(AWSFileManager::getBucket()) . "/builds/" . $this->id;
	}

	public function getID() {
		return $this->id;
	}

	public function getAuthor() {
		return $this->getBLID();
	}

	public function getBLID() {
		return $this->blid;
	}

	public function getTitle() {
		return $this->getName();
	}

	public function getName() {
		return $this->name;
	}

	public function getBrickCount() {
		return $this->getBricks();
	}

	public function getBricks() {
		return $this->bricks;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getFilename() {
		return $this->filename;
	}

	public function getTotalDownloads() {
		return StatManager::getTotalBuildDownloads($this->id);
	}

	public function getURL() {
		return $this->url;
	}
}
?>
