<?php


class FO_FEEDBACKS {
	/**
	 * FO_FEEDBACKS constructor.
	 */
	public function __construct() {

		$this->init_hooks();

	}

	function get_the_members_by_taxonomy( $post ) {
		$peer_review_id = null;

		$the_post = get_post( $post );
		if($the_post) {
			$peer_review_id = $the_post->ID;
		}
		if ( ! $peer_review_id ) {
			$peer_review_id = get_the_ID();
		}
		$term_list = wp_get_post_terms( $peer_review_id, 'team-pr', array( "fields" => "slugs" ) );
		$a         = $term_list[0];

		if ( get_post_meta( $peer_review_id, 'list') == false ) {

			$args = array(
				'post_type'   => 'member',
				'post_status' => 'publish',
				'tax_query'   => array(
					array(
						'taxonomy' => 'team-member',
						'field'    => 'slug',
						'terms'    => $a,
					)
				)
			);

			$posts_array = get_posts( $args );


			update_post_meta( $peer_review_id, 'list', $posts_array );
		}


		$receivers_table = get_post_meta( $peer_review_id, 'list', true );

		return $receivers_table;
	}

	function fo_members_options( $options, $title ) {
		$post = isset( $_GET['post'] ) ? $_GET['post'] : false;  // trovo l'id

		if ( $post ) {

			$options = array();


			if ( 'Team of the PR' === $title || 'Team of the member' === $title ) {
				 $options = $this->build_teams_options( $options );
			}

			if ( 'Receiver' === $title ) {

				$posts_array = $this->get_the_members_by_taxonomy( $post );
				foreach ( $posts_array as $post2 ) {
					$options[] = array(
						'#value' => $post2->ID,
						'#title' => $post2->post_title,
					);
				}
			}

			if ( 'Giver' === $title ) {
				$options = array();

				$args = array(
					'post_type'   => 'member',
					'post_status' => 'publish',

				);


				$posts_array = get_posts( $args );

				/* @var $post WP_Post */
				foreach ( $posts_array as $post2 ) {
					$options[] = array(
						'#value' => $post2->ID,
						'#title' => $post2->post_title,
					);

				}
			}


		}

		return $options;

	}

	function get_the_reviews() {
		$team = $this->get_the_slug();


		$args_by_team = array(
			'post_type'   => array( 'peer-review' ),
			'post_status' => array( 'publish' ),
			'tax_query'   => array(
				array(
					'taxonomy' => 'team-pr',
					'field'    => 'slug',
					'terms'    => $team,
				)
			)
		);

		$reviews_array = get_posts( $args_by_team );  //trovo tutte le pr appartenenti a un certo team

		$table_data_2 = array();
		$links        = array();
		foreach ( $reviews_array as $review ) {
			$review_id            = $review->ID;
			$link                 = get_permalink( $review->ID );
			$peer_review_date     = get_post_meta( $review_id, 'wpcf-date', true );
			$array_of_dates[]     = $peer_review_date;
			$table_data_2_content = $this->feedback_counter_of_the_peer_review( $review_id );

			$table_data_2[ $peer_review_date ] = ( $table_data_2_content );
			$links[ $peer_review_date ]        = $link;


		}
		$this->make_the_table_2( $table_data_2, $links );

	} //trova tutte le pr del team

	function feedback_counter_of_the_peer_review( $peer_review_id ) {
		$counter = array();
		list( $table_data, $receivers, $givers ) = $this->get_the_feedbacks( $peer_review_id );
		foreach ( $receivers as $receiver_a ) {
			$counter[ $receiver_a ] = 0;
		}
		foreach ( $givers as $giver_a ) {
			$giver_name = $giver_a['name'];
			foreach ( $receivers as $key => $receiver_a ) {
				if ( isset ( $table_data[ $receiver_a ][ $giver_name ] ) ) {
					$counter[ $receiver_a ] = $counter[ $receiver_a ] + 1;
				}
			}
		}

		return $counter;
	}  //conta i fb di una peer review x ciscun membro del team

	function make_summary_table() {
		$table_data_3 = $this->get_the_list_of_the_teams();
		?>
		<table border="1" cellpadding="10">
			<tbody>
			<?php foreach ( $table_data_3 as $team ): ?>
				<tr>
					<td><a href= <?php echo $team['url'] ?>> <?php echo $team['title']; ?></a></td>
				</tr>

			<?php endforeach; ?>
			</tbody>
		</table>


		<?php
	}  //make table 3

	function make_the_table_1() {
		$peer_review_id = get_the_ID();
		$counter        = array();

		list( $table_data, $receivers, $givers ) = $this->get_the_feedbacks( $peer_review_id );
		?>
		<!-- format them inside the table -->
		<table border="1" cellpadding="10">
			<thead>
			<tr>
				<th></th>
				<?php foreach ( $receivers as $receiver_a ): ?>
					<th><?php echo $receiver_a; ?></th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $givers as $giver_a ): ?>

				<tr>
					<td><?php $giver_name = $giver_a['name'];
						echo $giver_name; ?></td> <!-- loop they time -->
					<?php foreach ( $receivers as $key => $receiver_a ):
						?>
						<td>
							<?php
							if ( ! isset ( $table_data[ $receiver_a ][ $giver_name ] ) ) {
								echo '**********';
							} else {
								echo $table_data[ $receiver_a ][ $giver_name ];
								$counter[ $receiver_a ] = $counter[ $receiver_a ] + 1;
							}
							?>
						</td>
					<?php endforeach; ?>

				</tr>

			<?php endforeach; ?>


			<td>Totale</td>

			<?php foreach ( $receivers as $key => $receiver_a ):
				?>
				<td>
					<?php
					echo $counter[ $receiver_a ];
					?>
				</td>
			<?php endforeach; ?>


			</tbody>
		</table>


		<?php
		$this->feedback_counter_of_the_peer_review( $peer_review_id );
	}

	function get_the_slug() {

		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );

		$url = basename( $current_url );

		return $url;

	}

	function enqueue_scripts() {
		wp_register_script( 'fo-feedbacks', FO_FEEDBACKS_URL . '/res/js/main.js' );

		$data = array(
			'message' => __( "Giver and Receiver can't have the same value.", 'fo-feedbacks' ),
		);
		wp_localize_script( 'fo-feedbacks', 'fo_feedbacks_data', $data );
		if ( ! is_home() ) {
			wp_enqueue_script( 'fo-feedbacks' );
		}
	}

	function get_the_feedbacks( $peer_review_id ) {
		$table_data  = array();
		$receivers   = array();
		$givers      = array();
		$args        = array(
			'post_type'   => array( 'feedback' ),
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				array(
					'key'     => '_wpcf_belongs_peer-review_id',
					'value'   => $peer_review_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
			),
		);
		$query       = new WP_Query( $args );
		$posts_array = $query->get_posts();
		foreach ( $posts_array as $post ) {
			$receiver                          = $this->get_the_receiver_name( $post );
			$giver                             = $this->get_the_giver_name( $post );
			$opinion                           = get_post_meta( $post->ID, 'wpcf-opinion', true );
			$table_data[ $receiver ][ $giver ] = $opinion;
			$receivers[]                       = $receiver;
			$givers[]                          = $giver;
		}
		$receivers     = array_unique( $receivers );
		$givers_better = $this->sort_givers_by_team_and_name( $givers );

		return array( $table_data, $receivers, $givers_better );
	} //trova tutti i fb di una pr

	function retrieve_the_term() {
		$post        = isset( $_GET['post'] ) ? $_GET['post'] : false;  // trovo l'id
		$custom      = get_post_custom( $post ); //trovo il team del feedback
		$belong_team = $custom['wpcf-team-of-the-pr'];//todo: se non è impostato il team esce messaggio di errore-->controlla.
		return $belong_team;
	}

	function sort_givers_by_team_and_name( $givers ) {
		$peer_review_id = get_the_ID();
		$term_list      = wp_get_post_terms( $peer_review_id, 'team-pr', array( "fields" => "slugs" ) );
		if ( ! isset( $team ) ) {
			$team = implode( " ", $term_list );
		}


		$givers_and_team = array();
		foreach ( $givers as $giver ) {
			$team_id        = $team;
			$giver_and_team = array(
				"team_id" => $team_id,
				"name"    => $giver
			);

			$giver_and_team['team'] = $team_id;
			$givers_and_team[]      = $giver_and_team;
		}
		$sort = array();
		foreach ( $givers_and_team as $k => $v ) {
			$sort['teams'][ $k ] = $v['team'];
			$sort['names'][ $k ] = $v['name'];
		}
		array_multisort( $sort['teams'], SORT_ASC, $sort['names'], SORT_ASC, $givers_and_team );

		return $givers_and_team;
	}

	function get_the_list_of_the_teams() {
		$url   = site_url();
		$info  = array();
		$terms = get_terms( 'team-member', 'orderby=count&hide_empty=0' );
		/* @var $term WP_Term */
		foreach ( $terms as $term ) {
			$string                 = $term->term_id;
			$url_of_the_page        = $url . '/reporter-' . $string;
			$info[ $term->term_id ] = array(
				'title' => $term->name,
				'url'   => $url_of_the_page,
			);
		}

		return $info;
	} //lista di tutti i team

	function make_the_table_2( $table_data_2, $links )  //make table 2
	{
		/** @var WP_Query $wp_query */
		global $wp_query;

		if ( $wp_query->is_tax( 'team-pr' ) && $wp_query->is_archive() ) {
			$term_id = $wp_query->get_queried_object_id();

			$team_term = get_term( $term_id );
			if ( $team_term && ! is_wp_error( $team_term ) ) {
				$team              = $team_term->slug;
				$receivers         = array();
				$get_the_receivers = get_option( $team ); //leggo le options del team e trovo i receivers
				foreach ( $get_the_receivers as $get_the_receiver ) {
					$receivers[] = $get_the_receiver->post_title;
				}
				array_unique( $receivers );
				$array_of_dates = array_keys( $table_data_2 );


				?>

				<table border="1" cellpadding="10">
					<thead>
					<tr>
						<th></th>
						<?php foreach ( $receivers as $receiver_a ): ?>
							<th><?php echo $receiver_a; ?></th>
						<?php endforeach; ?>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $array_of_dates as $date ): ?>

						<tr>
							<td><a href="<?php echo $links[ $date ]; ?>"><?php
								echo date( 'Y-m-d', $date ); ?></td> <!-- loop they time -->
							<?php foreach ( $receivers as $receiver_a ):
								?>
								<td>
									<?php
									if ( ! isset ( $table_data_2[ $date ][ $receiver_a ] ) ) {
										echo '**********';
									} else {
										echo $table_data_2[ $date ][ $receiver_a ];
									}
									?>
								</td>
							<?php endforeach; ?>

						</tr>

					<?php endforeach; ?>
					</tbody>
				</table>

				<?php
			}
		}
	}


	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	function get_the_giver_name( $post ) {
		return $this->get_member_name( $post, 'giver' );
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	function get_the_receiver_name( $post ) {
		return $this->get_member_name( $post, 'receiver' );
	}

	function get_member_name( $post, $key ) {
		$member_id = get_post_meta( $post->ID, 'wpcf-' . $key, true );

		return get_the_title( $member_id );
	}

	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_shortcode( 'get-the-table', array(
			$this,
			'make_the_table_1'
		) ); //questo è da mettere nelle pagine delle singole pr
		add_shortcode( 'get-the-table-2', array(
			$this,
			'get_the_reviews'
		) ); //questo è da mettere nelle pagine dei team
		add_shortcode( 'summary', array( $this, 'make_summary_table' ) ); //questo è da mettere nel riassunto
		add_shortcode( 'get_it', array(
			$this,
			'get_the_members_by_taxonomy'
		) ); //questo è da mettere nelle pagine delle singole pr. Eventualmente.


		add_filter( 'wpt_field_options', array( $this, 'fo_members_options' ), 10, 2 );
	}

	/**
	 * @param $options
	 *
	 * @return array
	 */
	private function build_teams_options( $options ) {
		$terms = get_terms( 'team', 'orderby=count&hide_empty=0' );
		/* @var $term WP_Term */
		foreach ( $terms as $term ) {
			$options[] = array(
				'#value' => $term->term_id,
				'#title' => $term->name,
			);
		}

		return $options;
	}

}
