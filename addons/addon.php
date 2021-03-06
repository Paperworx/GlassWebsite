<?php
	require_once(realpath(dirname(__DIR__) . "/private/class/BoardManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/AddonObject.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/CommentManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/ScreenshotManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserManager.php"));
	require_once(realpath(dirname(__DIR__) . "/private/class/UserLog.php"));
//	require_once(realpath(dirname(__DIR__) . "/private/class/UserHandler.php"));
	require_once(realpath(dirname(__DIR__) . "/private/lib/Parsedown.php"));

	//to do: use ajax/json to build data for page
	//this php file should just format the data nicely
	if(isset($_GET['id'])) {
		try {
			$addonObject = AddonManager::getFromId($_GET['id'] + 0);
			$boardObject = BoardManager::getFromID($addonObject->getBoard());
		} catch(Exception $e) {
			//board doesn't exist
			header('Location: /addons');
			die("addon doesnt exist");
		}
	} else {
		header('Location: /addons');
		die();
	}

	if($addonObject->isRejected()) {
		include 'rejected.php';
		die();
	} else if(!$addonObject->getApproved()) {
		include 'unapproved.php';
		die();
	}

	if(isset($_POST['comment'])) {
		CommentManager::submitComment($addonObject->getId(), UserManager::getCurrent()->getBLID(), $_POST['comment']);
	}

	$_PAGETITLE = "Glass | " . htmlspecialchars($addonObject->getName());

	include(realpath(dirname(__DIR__) . "/private/header.php"));
	include(realpath(dirname(__DIR__) . "/private/navigationbar.php"));
?>
<script type="text/javascript">
$(document).ready(function() {
		var avgRating = 0<?php echo @round($addonObject->getRating()); ?>;

		for(var i = 0; i < avgRating; i++) {
			$('#star' + (i+1)).attr("src","/img/icons32/star.png");
		}

		for(var i = avgRating-1; i < 5; i++) {
			$('#star' + (i+2)).attr("src","/img/icons32/draw_star.png");
		}

		<?php if(UserManager::getCurrent()) { ?>
		$('.star').hover(function(){
			var starNum = $(this).attr('id').slice(4,5);
			for(var i = 0; i < starNum; i++) {
				$('#star' + (i+1)).attr("src","/img/icons32/star.png");
			}

			for(var i = starNum-1; i < 5; i++) {
				$('#star' + (i+2)).attr("src","/img/icons32/draw_star.png");
			}
		},function(){

		});


		$('#stars').mouseleave(function(){
			var starNum = avgRating;
			for(var i = 0; i < starNum; i++) {
				$('#star' + (i+1)).attr("src","/img/icons32/star.png");
			}

			for(var i = starNum-1; i < 5; i++) {
				$('#star' + (i+2)).attr("src","/img/icons32/draw_star.png");
			}
		});

		$('.star').click(function() {
			var starNum = $(this).attr('id').slice(4,5);
			$.post("/ajax/submitRating.php", {"rating": starNum, "aid": <?php echo $_GET['id']; ?>}, function(data, status) {
				avgRating = data;
				var starNum = avgRating;
				for(var i = 0; i < starNum; i++) {
					$('#star' + (i+1)).attr("src","/img/icons32/star.png");
				}

				for(var i = starNum-1; i < 5; i++) {
					$('#star' + (i+2)).attr("src","/img/icons32/draw_star.png");
				}
			});
		});
		<?php } ?>
	});
</script>
<div class="maincontainer">
	<?php
		echo "<span style=\"font-size: 9pt;\"><a href=\"/addons/\">Add-Ons</a> >> ";
		echo "<a href=\"board.php?id=" . $boardObject->getID() . "\">" . htmlspecialchars($boardObject->getName()) . "</a> >> ";
		echo "<a href=\"#\">" . htmlspecialchars($addonObject->getName()) . "</a></span>";

		if($current = UserManager::getCurrent()) {
			if($current->inGroup("Moderator")) {
				echo "<div style=\"background-color: #aabbcc; padding: 10px; border-radius:10px; margin-top:10px; text-align:center\"><a href=\"moderate.php?id=" . $addonObject->getId() . "\">Moderator Settings</a></div>";
			}
		}

		echo "<h2>" . htmlspecialchars($addonObject->getName()) . "</h2>";
	?>
	<div style="margin-bottom: 15px; display: inline-block; width: 100%; font-size: 0.8em">
		<div class="addoninfoleft">
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/user.png" /> By <?php
			$authors = $addonObject->getAuthorInfo();

			if(sizeof($authors) == 1) {
				//$uo = new UserHandler();
				//$uo->initFromId($authors[0]->id);
				$uo = UserManager::getFromBLID($authors[0]->blid);
				echo "<a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>";
			} else if(sizeof($authors) == 2) {
				//we cant use UserHandler here because we may not have accounts for all

				$name1 = UserLog::getCurrentUsername($authors[0]->blid);
				if($name1 === false) {
					$name1 = "Blockhead" . $authors[0]->blid;
				}
				$name2 = UserLog::getCurrentUsername($authors[1]->blid);
				if($name2 === false) {
					$name2 = "Blockhead" . $authors[1]->blid;
				}
				echo "<a href=\"/user/view.php?blid=" . $authors[0]->blid . "\">" . htmlspecialchars($name1) . "</a>";
				echo " and ";
				echo "<a href=\"/user/view.php?blid=" . $authors[1]->blid . "\">" . htmlspecialchars($name2) . "</a>";
			} else {
				var_dump($authors);
				$count = sizeof($authors);
				foreach($authors as $num=>$author) {
					//$uo = new UserHandler();
					//$uo->initFromId($auth->id);
					$uo = UserManager::getFromBLID($author->blid);

					if($count-$num == 1) {
						echo "and <a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>";
					} else {
						echo "<a href=\"#\">" . htmlspecialchars($uo->getName()) . "</a>, ";
					}
				}
			}
			?>
			<br />
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/folder_vertical_zipper.png" />
			<?php
			echo $addonObject->getFilename();
			?>
			<br />
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/accept_button.png" />
			Approved
			<br />
			<image style="height:1.5em" src="http://blocklandglass.com/img/icons32/inbox_upload.png" />
			<?php echo date("F j, g:i a", strtotime($addonObject->getUploadDate())); ?>
			<br />
			<br />
			<div id="stars" style="cursor:pointer;">
				<image style="height:1.2em" class="star" id="star1" src="/img/icons32/draw_star.png" />
				<image style="height:1.2em" class="star" id="star2" src="/img/icons32/draw_star.png" />
				<image style="height:1.2em" class="star" id="star3" src="/img/icons32/draw_star.png" />
				<image style="height:1.2em" class="star" id="star4" src="/img/icons32/draw_star.png" />
				<image style="height:1.2em" class="star" id="star5" src="/img/icons32/draw_star.png" />
			</div>
		</div>
		<div class="addoninforight">
			<?php
			echo $addonObject->getDownloads(0);
			?>
			 <image style="height:1.5em" src="http://blocklandglass.com/img/icons32/inbox_download.png" /><br />
			<br />
			<?php
			$tagIDs = TagManager::getTagsFromAddonID($addonObject->getId());
			$tags = array();
			foreach($tagIDs as $tid) {
				$tags[] = TagManager::getFromId($tid);
			}

			foreach($tags as $tag) {
				echo $tag->getHTML();
			}
			?>
			<br />
			<a href="review/code.php?id=<?php echo $addonObject->getId() ?>">View source code</a>
		</div>
	</div>
	<hr />
	<p>
		<?php
			$Parsedown = new Parsedown();
			$Parsedown->setBreaksEnabled(true);
			$Parsedown->setMarkupEscaped(true);

			//External links appearing in the description should open in a new tab and switch to that tab instead of replacing the current one
			echo $Parsedown->text($addonObject->getDescription());
		?>
	</p>
	<hr />
	<div style="text-align: center">
		<?php
		$version = $addonObject->getVersion();
		$id = "stable";
		$class = "green";
		echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=0" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />v' . $version . '</span></a>';
		if($addonObject->hasBeta()) {
			$id = "beta";
			$class = "red";
			echo '<a href="/addons/download.php?id=' . $addonObject->getId() . '&beta=1" class="btn dlbtn ' . $class . '"><b>' . ucfirst($id) . '</b><span style="font-size:9pt"><br />v' . $addonObject->getBetaVersion() . '</span></a>';
		}
		?>
	</div>
	<div class="screenshots" style="text-align:center;margin: 0 auto">
		<?php
		$screenshots = ScreenshotManager::getScreenshotsFromAddon($_GET['id']);
		if(sizeof($screenshots) > 0) {
			echo "<hr />";
		}
		foreach($screenshots as $sid) {
		  $ss = ScreenshotManager::getFromId($sid);
		  echo "<div style=\"padding: 5px; margin: 10px 10px; background-color: #eee; display:inline-block; width: 128px; vertical-align: middle\">";
		  echo "<a target=\"_blank\" href=\"/addons/screenshot.php?id=" . $sid . "\">";
		  echo "<img src=\"" . $ss->getThumbUrl() . "\" /></a>";
		  echo "</div>";
		}
		?>
	</div>
	<?php
		$deps = DependencyManager::getDependenciesFromAddonID($_GET['id']);
		if(sizeof($deps) > 0) {
			echo "<hr /><div style=\"text-align:center\">";
			echo "This add-on has some dependencies, or add-ons that it requires to run!<br/>";
			foreach($deps as $did) {
				$dep = DependencyManager::getFromId($did);
				$rid = $dep->getRequired();
				$requiredAddon = AddonManager::getFromId($rid);
				echo "<div style=\"padding: 10px; background-color: #ffbbbb; display: inline-block; border-radius: 5px\"><a href=\"addon.php?id=" . $requiredAddon->getId() . "\">" . $requiredAddon->getName() . "</a></div>";
			}
			echo "</div>";
		}
	?>
	<hr />
	<div class="comments" id="commentSection">
		<form action="" method="post">
			<?php include(realpath(dirname(__DIR__) . "/ajax/getComments.php")); ?>
		</form>
	</div>
</div>
<?php include(realpath(dirname(__DIR__) . "/private/footer.php")); ?>
