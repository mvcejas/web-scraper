<?php
	include 'simple_html_dom.php';

	$url = 'http://www.mojo-code.com/categories/wordpress/';

	$html = file_get_html($url);

	$count = 0;
	foreach($html->find('section article.item-entry') as $article){
		if($count<1){
			echo $article;
			echo "<h3><a href='".$article->find('h3 a',0)->href."?r=lgwpthemes'>".$article->find('h3',0)->plaintext."</a></h3>";
			echo "<img src='".$article->find('img',0)->src."'>";
			echo "<div>".$article->find('.deposit-price',0)->plaintext."</div>";
			echo "<h3><a href='http://www.mojo-code.com".$article->find('.price a',0)->href."?r=lgwpthemes'>".$article->find('h3',0)->plaintext."</a></h3>";
		}
		$count++;
	}
?>
