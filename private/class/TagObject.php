<?php
class TagObject {
	public $id;
	public $name;
	public $base_color;
	public $icon;

	//whether or not we should keep this tag if no add-ons are pointing to it
	private $important;

	public function __construct($resource) {
		$this->id = intval($resource->id);
		$this->name = $resource->name;
		$this->base_color = $resource->base_color;
		$this->icon = $resource->icon;
		$this->important = intval($resource->important);
	}

	public function getID() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	//As opposed to getBaseColor()
	public function getColor() {
		return $this->base_color;
	}

	public function getBorderColor() {
		//assuming the format ceffce
		return str_replace("ce", "99", $this->base_color);
	}

	public function getIcon() {
		return $this->icon;
	}

	public function getImportant() {
		return $this->important;
	}

	public function isImportant() {
		return $this->getImportant();
	}

	public function getHTML() {
		return "<a href=\"/addons/search.php?tag=" . $this->getId() . "\" class=\"tag\" style=\"background-color: #" . $this->getColor() . "; border: 2px solid #" . $this->getBorderColor() . ";\"><img style=\"padding-right: 4px;\" src=\"https://blocklandglass.com/img/icons16/" . $this->getIcon() . ".png\">" . $this->getName() . "</a>";
	}
}
?>
