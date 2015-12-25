$(function(){
	$("form")
	.find("input").keyup(function(){
		var keyword = $(this).val();
		var id = $(this).parents('form:first').attr('id');

		if( id == 'bankForm'){
			keyword.length ? showSomeBank(":contains('" + keyword + "')") : showSomeBank();
			clearBranch();
		}

		if( id == 'branchForm'){
			keyword.length ? showSomeBranch(":contains('" + keyword + "')") : showSomeBranch();
		}
	}).end()
	.find("button").click(function(){
		var id = $(this).parents('form:first').attr('id');
		$(this).parents('form').find('input').val('');
		
		if( id == 'bankForm'){					
			showSomeBank();
			clearBranch();
		}

		if( id == 'branchForm'){
			showSomeBranch();
		}
	})

	$("#bankList a").click(function(){
		var $bank = $(this).parents('.bank');
		// var $template = $("#branchList .template").clone().show().removeClass('template');
	
		$.getJSON('index.php', {'bankNo': $(this).data('no') }, function(data){
			showSomeBank($bank);

			//branch
			clearBranch();
			$.each(data, function(branchNo, branch){
				cloneBranchTemplate(branch).appendTo('#branchList');
			});
			$("#branchList").show();
		})
	});

	function showSomeBank(desc){
		$('html, body').scrollTop(0);
		if(desc){
			$("#bankList .bank").show().not(desc).hide();
		}else{
			$("#bankList .bank").show();
		}
	}
	function showSomeBranch(desc){
		if(desc){
			$("#branchList .branch").not(".template").show().not(desc).hide();
		}else{
			$("#branchList .branch").not(".template").show();
		}
	}
	function clearBranch(){
		$("#branchList .branch").not(".template").remove();
		$("#branchList").hide();
	}
	function cloneBranchTemplate(branch){
		return $("#branchList .template").clone()
				.show()
				.removeClass('template')
				.find('.no').text(branch.no).end()
				.find('.name').text(branch.name).end()
				.find('.abbr').text(branch.abbr).end();
	}
});