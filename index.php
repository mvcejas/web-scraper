<!DOCTYPE html>
<html>
<head>
  <title>Web Crawler</title>
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>
	<script>
	$(document).ready(function(){
		var parsedURLs = {};
		var parsedXMLs = [];
		var i = 0;

		$('form').submit(function(e){
			e.preventDefault();
			var action = $(this).attr('action');
			var data = $(this).serialize();			
			$.post(action,data,function(response){				
				parsedURLs =  response;
				$.each(parsedURLs,function(a,b){
					$('#status').prepend('<div class="alert alert-danger">'+b+'</div>');
				});
				XMLized();
			},'json').error(function(){
				$('#status').prepend('<div class="alert alert-danger">Error: something went wrong.</div>');
			});

			$('#status').html('').prepend('<div class="alert alert-success">Scraping list URLs. Please wait, this may take a few moment...</div>');
			return false;
		});

		function XMLized(){			
			if(i<parsedURLs.length){
				$.post('crawl.php',{parseURL:parsedURLs[i]},function(response){
					console.log(response);
					if(response.status){
						parsedXMLs.push(response.data);
						$('#status').prepend('<div class="alert alert-info">Found: '+response.data+'</div>');
					}
					else{
						$('#status').prepend('<div class="alert alert-error">'+response.data+'</div>');
					}	
					XMLized();
					i++;
				},'json');	
				$('#progressbar').html('<div class="progress progress-striped active"><div class="bar" style="width:'+((100/parsedURLs.length)*(i+1))+'%"></div></div>');				
			}
			else{
				i=0; // reset counter
				StartScraping(); // start scraping data
			}			
		}

		function StartScraping(){		
			console.log('Started scraping...');	
			if(i<parsedXMLs.length){
				$.post('crawl.php',{parseXML:parsedXMLs[i]},function(response){
					console.log(response);
					if(response.status){
						$('#status').prepend('<div class="alert alert-success">'+response.data+'</div>');
					}
					else{
						$('#status').prepend('<div class="alert alert-error">'+response.data+'</div>');
					}
					StartScraping();
					console.log(i);
					i++;
				},'json');
				$('#progressbar').html('<div class="progress progress-striped active"><div class="bar bar-success" style="width:'+((100/parsedURLs.length)*i)+'%"></div></div>');			
			}
			else{
				$.post('crawl.php',{export:1});
			}
		}

		$('#throbber').ajaxStart(function(){
			$(this)
			.show()
			.removeClass('alert-success')
			.addClass('alert-info')
			.text('Scraping data to target. Please wait, this may take a few moment.')
			.fadeIn();			
		}).ajaxStop(function(){
			$(this)
			.hide()
			.removeClass('alert-info')
			.addClass('alert-success')
			.text('Data scraping completed!')
			.fadeIn();			
		});

	});
	</script>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="span6">
			<form action="crawl.php" method="post">
				<fieldset>
					<legend>Enter URL to scrape</legend>
					<div class="controls">
						<div class="alert alert-info hide" id="throbber">Scraping data to the target. Please wait, this may take a few moment.</div>
					</div>
					<div id="progressbar"></div>
					<div class="control-group">
						<div class="controls input-append">
							<input type="text" class="input-xlarge span6" name="URL" value="https://icecat.biz/index.cgi?price=&amp;limit_value_1=&amp;feature_id_1=2196&amp;limit_value_2=&amp;feature_id_2=5&amp;limit_value_3=&amp;feature_id_3=6&amp;limit_value_4=&amp;feature_id_4=3318&amp;limit_value_5=&amp;feature_id_5=1585&amp;limit_value_6=&amp;feature_id_6=944&amp;limit_value_7=&amp;feature_id_7=3233&amp;limit_value_8=&amp;feature_id_8=94&amp;limit_value_9=&amp;feature_id_9=8400&amp;limit_value_10=&amp;feature_id_10=6694&amp;limit_value_11=&amp;feature_id_11=838&amp;stock=-2&amp;292=292&amp;language=en&amp;rows=11&amp;uncatid=43171801&amp;new_search=1">
							<button type="submit" class="btn btn-primary">Go!</button>
						</div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="span7" id="status">			
		</div>
	</div>
</div>
</body>
</html>
