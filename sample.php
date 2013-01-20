<?php
  include 'simple_html_dom.php';

	$url = 'http://www.mojo-code.com/categories/wordpress/';

	$html = file_get_html($url);

	foreach($html->find('section article.item-entry') as $article){
			echo "<h3>".$article->find('h3',0)->plaintext."</h3>";
			echo "<img src='".$article->find('img',0)->src."'>";
			echo "<div>".$article->find('.deposit-price',0)->plaintext."</div>";
	}
?>
