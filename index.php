<?php
require "vendor/autoload.php";

$bank = new Asper\Util\BankNoParser();

if( isset($_GET['bankNo']) && strlen($_GET['bankNo']) ){
	$branchList = $bank->branchListByMainNo($_GET['bankNo']);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($branchList,  JSON_PRETTY_PRINT);
	exit;
}

$bankList = $bank->bankList();
?>
<!DOCTYPE html>
<html lang="">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>銀行代號查詢</title>
		<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
		<style>
			body{ padding-left:20px; padding-right:20px; }

			.bank .form-control, .branch .form-control{ height:auto;margin-bottom:10px; overflow:hidden; white-space: nowrap;}

			
			.title{ font-size:1.5em; font-weight: bolder; margin-left:30px; margin-right:20px; }
			.no{ font-size:1.5em; text-align:center; line-height:1.8em; }
			.abbr{ font-size:0.8em; color:#555;  }

			#branchList{ display: none;}
		</style>
	</head>
	<body>
		<div class="container-fluid">			
			<div class="row" id='bankList'>
				
				<div class="row well well-sm">
					<form class="form-inline" id="bankForm">
						<div class="form-group">
							<label class='title'>銀行代號一覽表</label>
						</div>
						<div class="form-group">
							<label class="">銀行搜尋</label>
							<input type="text" class="form-control" placeholder="銀行搜尋">							
						</div>
						<button type="button" class='btn btn-info'>顯示全部銀行</button>
					</form>
				</div>

				<?php foreach($bankList as $no=>$bank):?>
				<div class="col-sm-4 col-xs-6 bank">
					<div class="form-control row">
						<span class="no col-sm-4 col-xs-5 row">
							<a data-no="<?php echo $bank['no'];?>"><?php echo $bank['no'];?></a>
						</span>
						<span class="col-sm-8 col-xs-7" title="<?php echo $bank['name'];?>">
							<div class="name row">
								<?php echo $bank['name'];?>
							</div>
							<div class="abbr row">
								<?php echo $bank['abbr'];?>
							</div>
						</span>
					</div>
				</div>
				<?php endforeach;?>
			</div>

			<div class="row" id='branchList'>
				<div class="row well well-sm">
					<form class="form-inline" id='branchForm'>
						<div class="form-group">
							<label class='title'>分行代號一覽表</label>
						</div>
						<div class="form-group">
							<label class="">分行搜尋</label>
							<input type="text" class="form-control" placeholder="分行搜尋">							
						</div>
						<button type="button" class='btn btn-info'>顯示全部分行</button>
					</form>
				</div>

				<div class="col-sm-4 col-xs-6 branch template" style='display: none;'>
					<div class="form-control row">
						<span class="no col-sm-7 col-xs-7 row">
						</span>
						<span class="col-sm-5 col-xs-5">
							<div class="name row"></div>
							<div class="abbr row"></div>
						</span>
					</div>
				</div>
			</div>
		</div>

		<script src="//code.jquery.com/jquery.js"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		<script src="assets/site.js"></script>
	</body>
</html>