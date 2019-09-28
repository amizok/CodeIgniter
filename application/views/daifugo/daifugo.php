<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>大富豪</title>
	<?php echo link_tag('/assets/css/daifugo.css'); ?>
	<link href="https://fonts.googleapis.com/css?family=Quicksand&display=swap" rel="stylesheet">

</head>
<body>
	<div class="cpu-area">
      <?php for($i = 0; $i < count($cpuCardLists); $i++) {
      	$cpuNo = ($i + 1);?>
		    <div class="cpu <?php echo $cpuNo ?>">CPU<?php echo $cpuNo ?>
				<div class="hand">
					<?php foreach ($cpuCardLists[$i] as $cCard) :?>
						<?php $card_prop = array(
											'src' => $back,
											'width' => '40',
											'height' => 'auto'
									);
						echo img($card_prop); ?>
					<?php endforeach; ?>
				</div>
			</div>
      <?php }?>
	</div>
	<div class="game-area">
		<div class="cards" id="game-cards">
	      	<?php for($i = 0; $i < 4; $i++) {?>
	      		<?php 
	      			$index = $i + 1;
	      			$l = 0;
	      			$t = 0;
	      			if ($index % 2 == 0) {
	      				$l = "50%";
	      				$t = "26%";
	      				if ($index % 4 == 0) {
	      					$l = "42%";
	      					$t = "30%";
	      				}
	      			} else {
	      				$l = "44%";
	      				$t = "auto";
	      				if ($index % 3 == 0) {
	      					$l = "48%";
	      					$t = "36%";
	      				}
	      			}
	      		?>
				<div class="card img" id="location-<?php echo $index;?>"
					style="z-index:<?php echo $index;?>;
							left:<?php echo $l; ?>;
							top:<?php echo $t ?>;">
					<?php
					$card_prop = array(
									'src' => 'assets/img/cards/spade_1.png',
									'id' => 'img-location-'."$index",
									'width' => '90',
									'height' => 'auto'
								);
					echo img($card_prop); ?>
				</div>
	      	<?php }?>
	    </div>
	</div>
	<div class="user-area">
                <div class="btn">
                    <a href="#" id="put" class="btn-square-so-pop">出す</a>
                    <a href="#" id="pass" class="btn-square-so-pop">パス</a>
                </div>
		<div class="hand" id="user-hand">
			<!-- <input type="checkbox" class="select-img"> -->
			<?php $index = 0; ?>
			<?php foreach ($userCardList as $card): ?>
				<?php 
				$card_prop = array(
									'src' => $card,
									'id' => 'img'.$index++,
									'width' => '90',
									'height' => 'auto'
							);
				echo img($card_prop); ?>
			<?php endforeach; ?>
		</div>
		<div class="status">
			<div class="icon">
				<?php
				$icon_prop = array(
							'src' => 'assets/img/testicon.png',
							'width' => '50',
							'height' => 'auto'
						);
				echo img($icon_prop);?>	
			</div>
			<div class="info">
					<div class="name">ユーザーネーム</div>
					<div class="current-class">平民</div>
			</div>
		</div>
	</div>
	<div>テスト</div>
	<div>
		<div>CPU1 <?php echo count($cpuCardLists[0])?>枚</div>
			<?php foreach ($cpuCardLists[0] as $card): ?>
				<?php echo $card; ?>
			<?php endforeach; ?>
		<div>CPU2 <?php echo count($cpuCardLists[1])?>枚</div>
			<?php foreach ($cpuCardLists[1] as $card): ?>
				<?php echo $card; ?>
			<?php endforeach; ?>
		<div>CPU3 <?php echo count($cpuCardLists[2])?>枚</div>
			<?php foreach ($cpuCardLists[2] as $card): ?>
				<?php echo $card; ?>
			<?php endforeach; ?>
	</div>
	<div>テスト</div>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/daifugo.js"></script>
</body>
</html>
