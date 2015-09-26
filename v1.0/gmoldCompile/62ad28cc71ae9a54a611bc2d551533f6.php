<?php /* GMold Compile file | CTime:2015-9-25*/ ?><!DOCTYPE html>
 <html>
 	<head>
 		<title><?php echo $this->data->title;?></title>
 		<meta charset="utf-8">
 	</head>
 	<body>
 		<h2>GMold!</h2>
 		OpenSource PHP Template engine v1.0
 		<hr>
 		<h4>Variable var</h4>
 			<?php echo $this->data->var;?>
 		<hr>
 		<h4>Conditional Statement -> if-else-endif</h4>
 			<?php if($this->data->ifistrue){ ?>
 			Variable ifistrue is True<br>
 			<?php } else { ?>
 			Variable ifistrue is False<br>
 			<?php }  ?>
 		<hr>
 		<h4>Conditional Statement -> if-elseif-else-endif</h4>
 			<?php if($this->data->ifvalueA){ ?>
 				Variable ifvalueA is True<br>
 			<?php } else if($this->data->ifvalueB){ ?>
 				Variable ifvalueB is True<br>
 			<?php } else { ?>
 				Variable ifvalueA 和 ifvalueB 均is False<br>
 			<?php }  ?>
 		<hr>
 		<h4>Conditional Statement -> switch-case-endswitch</h4>
 			<?php switch($this->data->switchval){default: ?>
 				Variable switchval neither A nor B<br>
 			<?php break;case 'A': ?>
 				Variable switchval not A<br>
 			<?php break;case 'B': ?>
 				Variable switchval not B<br>
 			<?php break;} ?>
 		<hr>
 	</body>
 </html>