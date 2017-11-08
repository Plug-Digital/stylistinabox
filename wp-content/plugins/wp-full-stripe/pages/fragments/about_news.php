<?php
/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.04.04.
 * Time: 9:52
 */
?>
<div class="news-feed">
	<section id="news">
		<?php
		/** @var array $news_feed */
		if ( count( $news_feed ) == 0 ) {
			echo '<h4>' . __( 'The newsfeed is not available at the moment.', 'wp-full-stripe' ) . '</h4>';
			echo '<h4>' . __( 'Please check back later.', 'wp-full-stripe' ) . '</h4>';
		} else {

			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			foreach ( $news_feed as $feed_entry ) {
				$published   = strtotime( $feed_entry['published'] );
				$title       = $feed_entry['title'];
				$description = $feed_entry['description'];
				$content     = $feed_entry['content'];
				$article     = '<article>';
				$article .= '<h4><span class="wp-ui-text-icon">[' . esc_html( date( "$date_format $time_format", $published ) ) . ']</span> - ' . esc_html( $title ) . '</h4>';
				$article .= $content;
				$article .= '</article>';
				echo $article;
			}
		}
		?>
	</section>
</div>
