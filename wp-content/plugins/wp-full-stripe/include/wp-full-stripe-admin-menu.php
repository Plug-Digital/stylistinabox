<?php

class MM_WPFS_Admin_Menu {

	const UPDATE_INTERVAL_4_HOURS = 14400;
	const UPDATE_INTERVAL_30_MINUTES = 1800;

	private $capability = 'manage_options';

	private /** @noinspection HtmlUnknownTarget */
		$settings_nav_tab_item_wrapper = '<a href="%s" class="%s">%s</a>';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'menu_pages' ) );
		if ( defined( 'WP_FULL_STRIPE_DEMO_MODE' ) ) {
			$this->capability = 'read';
		}
	}

	function admin_init() {
		wp_register_style( 'fullstripe-font-awesome-css', plugins_url( '/vendor/components/font-awesome/css/font-awesome.min.css', dirname( __FILE__ ) ), null, MM_WPFS::VERSION );
		wp_register_style( 'fullstripe-css', plugins_url( '/css/fullstripe.css', dirname( __FILE__ ) ), null, MM_WPFS::VERSION );
		wp_register_style( 'fullstripe-ui-css', plugins_url( '/css/fullstripe-ui.css', dirname( __FILE__ ) ), null, MM_WPFS::VERSION );
		wp_register_style( 'fullstripe-admin-css', plugins_url( '/css/fullstripe-admin.css', dirname( __FILE__ ) ), array( 'fullstripe-font-awesome-css' ), MM_WPFS::VERSION );
	}

	function menu_pages() {
		// Add the top-level admin menu
		$page_title = 'Full Stripe Settings';
		$menu_title = 'Full Stripe';
		$menu_slug  = 'fullstripe-settings';
		$capability = $this->capability;
		$function   = array( $this, 'fullstripe_settings' );
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );

		// Add submenu page with same slug as parent to ensure no duplicates
		$sub_menu_title = 'Settings';
		$menu_hook      = add_submenu_page( $menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function );
		add_action( 'admin_print_scripts-' . $menu_hook, array(
			$this,
			'fullstripe_admin_scripts'
		) ); //this ensures script/styles only loaded for this plugin admin pages

		$submenu_page_title = 'Full Stripe Payments';
		$submenu_title      = 'Payments';
		$submenu_slug       = 'fullstripe-payments';
		$submenu_function   = array( $this, 'fullstripe_payments' );
		$menu_hook          = add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		$submenu_page_title = 'Full Stripe Subscriptions';
		$submenu_title      = 'Subscriptions';
		$submenu_slug       = 'fullstripe-subscriptions';
		$submenu_function   = array( $this, 'fullstripe_subscriptions' );
		$menu_hook          = add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		/*
		$submenu_page_title = 'Full Stripe Transfers';
		$submenu_title      = 'Transfers';
		$submenu_slug       = 'fullstripe-transfers';
		$submenu_function   = array( $this, 'fullstripe_transfers' );
		$menu_hook          = add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );
		*/

		$submenu_page_title = 'Full Stripe Help';
		$submenu_title      = 'Help';
		$submenu_slug       = 'fullstripe-help';
		$submenu_function   = array( $this, 'fullstripe_help' );
		$menu_hook          = add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		$submenu_page_title = 'About WP Full Stripe';
		$submenu_title      = 'About';
		$submenu_slug       = 'fullstripe-about';
		$submenu_function   = array( $this, 'fullstripe_about_page' );
		$menu_hook          = add_submenu_page( $menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		//create forms page - don't show on submenu
		$submenu_page_title = 'Full Stripe Create Form';
		$submenu_title      = 'Create Form';
		$submenu_slug       = 'fullstripe-create-form';
		$submenu_function   = array( $this, 'fullstripe_create_form' );
		$menu_hook          = add_submenu_page( null, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		//edit forms page - don't show on submenu
		$submenu_page_title = 'Full Stripe Edit Form';
		$submenu_title      = 'Edit Form';
		$submenu_slug       = 'fullstripe-edit-form';
		$submenu_function   = array( $this, 'fullstripe_edit_form' );
		$menu_hook          = add_submenu_page( null, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		//create plans page - don't show on submenu
		$submenu_page_title = 'Full Stripe Create Plan';
		$submenu_title      = 'Create Plan';
		$submenu_slug       = 'fullstripe-create-plan';
		$submenu_function   = array( $this, 'fullstripe_create_plan' );
		$menu_hook          = add_submenu_page( null, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		//edit plans page - don't show on submenu
		$submenu_page_title = 'Full Stripe Edit Plan';
		$submenu_title      = 'Edit Plan';
		$submenu_slug       = 'fullstripe-edit-plan';
		$submenu_function   = array( $this, 'fullstripe_edit_plan' );
		$menu_hook          = add_submenu_page( null, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function );
		add_action( 'admin_print_scripts-' . $menu_hook, array( $this, 'fullstripe_admin_scripts' ) );

		do_action( 'fullstripe_admin_menus', $menu_slug );
	}

	function fullstripe_admin_scripts() {
		$options = get_option( 'fullstripe_options' );
		wp_enqueue_media();
		wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v2/', array( 'jquery' ) );
		wp_enqueue_script( 'sprintf-js', plugins_url( 'js/sprintf.min.js', dirname( __FILE__ ) ), null, MM_WPFS::VERSION );
		wp_enqueue_script( 'wp-full-stripe-admin-js', plugins_url( '/js/wp-full-stripe-admin.js', dirname( __FILE__ ) ), array(
			'sprintf-js',
			'jquery',
			'stripe-js',
			'jquery-ui-tabs',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-autocomplete',
			'jquery-ui-button',
			'jquery-ui-tooltip',
			'jquery-ui-sortable'
		), MM_WPFS::VERSION );
		if ( $options['apiMode'] === 'test' ) {
			wp_localize_script( 'wp-full-stripe-admin-js', 'stripekey', $options['publishKey_test'] );
		} else {
			wp_localize_script( 'wp-full-stripe-admin-js', 'stripekey', $options['publishKey_live'] );
		}
		wp_localize_script( 'wp-full-stripe-admin-js', 'admin_ajaxurl', admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'wp-full-stripe-admin-js', 'emailReceipts', json_decode( $options['email_receipts'] ) );

		wp_enqueue_style( 'fullstripe-css' );
		wp_enqueue_style( 'fullstripe-ui-css' );
		wp_enqueue_style( 'fullstripe-admin-css' );

		do_action( 'fullstripe_admin_scripts' );

	}

	function fullstripe_settings() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_admin_page.php';
	}

	function fullstripe_payments() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		if ( ! class_exists( 'WP_List_Table' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		if ( ! class_exists( 'WPFS_Named_Payments_Table' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( WP_FULL_STRIPE_DIR . '/include/wp-full-stripe-tables.php' );
		}

		$table = new WPFS_Named_Payments_Table();
		$table->prepare_items();

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_payments_page.php';
	}

	function fullstripe_subscriptions() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		if ( ! class_exists( 'WP_List_Table' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		if ( ! class_exists( 'WPFS_Multiple_Subscribers_Table' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( WP_FULL_STRIPE_DIR . '/include/wp-full-stripe-tables.php' );
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$subscribersTable = new WPFS_Multiple_Subscribers_Table();

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_subscriptions_page.php';
	}

	function fullstripe_transfers() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_transfers_page.php';
	}

	function fullstripe_help() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_help_page.php';
	}

	function fullstripe_create_form() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_create_form_page.php';
	}

	function fullstripe_edit_form() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_edit_form_page.php';
	}

	function fullstripe_about_page() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpUnusedLocalVariableInspection */
		$news_feed = $this->get_news_feed();

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_about_page.php';
	}

	private function get_news_feed( $force_reload = false ) {

		$news_feed             = get_transient( 'wpfs_news_feed' );
		$news_feed_last_update = get_transient( 'wpfs_news_feed_last_update' );

		$load_feed = false;
		if ( $news_feed === false ) {
			$load_feed = true;
		} elseif ( is_array( $news_feed ) && count( $news_feed ) == 0 ) {
			$load_feed = true;
		}
		if ( $news_feed_last_update === false ) {
			$load_feed = true;
		}
		if ( isset( $news_feed_last_update ) ) {
			$current_time    = time();
			$update_interval = self::UPDATE_INTERVAL_4_HOURS;
			if ( isset( $news_feed ) && count( $news_feed ) == 0 ) {
				$update_interval = self::UPDATE_INTERVAL_30_MINUTES;
			}
			if ( $current_time - $news_feed_last_update > $update_interval ) {
				$load_feed = true;
			}
		}
		if ( $load_feed || $force_reload ) {
			$news_feed = $this->load_news_feed( MM_WPFS_NewsFeed::URL );
			set_transient( 'wpfs_news_feed', $news_feed );
			set_transient( 'wpfs_news_feed_last_update', time() );
		}

		return $news_feed;
	}

	private function load_news_feed( $news_feed_url, $max_feed_length = 10 ) {
		$news_feed = array();
		try {

			$response = wp_remote_get( $news_feed_url );
			if ( ! is_wp_error( $response ) ) {
				$response_body = wp_remote_retrieve_body( $response );

				$parser = xml_parser_create_ns( 'UTF-8' );
				xml_parse_into_struct( $parser, $response_body, $values, $index );
				xml_parser_free( $parser );

				$feed_entry = null;

				for ( $i = 0; $i < count( $values ) && count( $news_feed ) < $max_feed_length; $i ++ ) {
					$value = $values[ $i ];
					if ( $value['tag'] == 'ITEM' ) {
						if ( $value['type'] == 'open' ) {
							$feed_entry = array();
						}
						if ( $value['type'] == 'close' ) {
							array_push( $news_feed, $feed_entry );
							$feed_entry = null;
						}
					}
					if ( $value['tag'] == 'TITLE' && $value['type'] == 'complete' ) {
						if ( isset( $feed_entry ) ) {
							$feed_entry['title'] = $value['value'];
						}
					}
					if ( $value['tag'] == 'DESCRIPTION' && $value['type'] == 'complete' ) {
						$feed_entry['description'] = $value['value'];
					}
					if ( $value['tag'] == 'HTTP://PURL.ORG/RSS/1.0/MODULES/CONTENT/:ENCODED' && $value['type'] == 'complete' ) {
						$feed_entry['content'] = $value['value'];
					}
					if ( $value['tag'] == 'PUBDATE' && $value['type'] == 'complete' ) {
						$feed_entry['published'] = $value['value'];
					}
					if ( $value['tag'] == 'CATEGORY' && $value['type'] == 'complete' ) {
						$feed_entry['category'] = $value['value'];
					}
					if ( $value['tag'] == 'LINK' && $value['type'] == 'complete' ) {
						$feed_entry['link'] = $value['value'];
					}
					if ( $value['tag'] == 'COMMENTS' && $value['type'] == 'complete' ) {
						$feed_entry['comments'] = $value['value'];
					}
				}

			}
		} catch ( Exception $e ) {
			error_log( sprintf( 'Message=%s, Stack=%s', $e->getMessage(), $e->getTraceAsString() ) );
		}

		return $news_feed;

	}

	function fullstripe_create_plan() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_create_plan_page.php';
	}

	function fullstripe_edit_plan() {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		/** @noinspection PhpIncludeInspection */
		include WP_FULL_STRIPE_DIR . '/pages/fullstripe_edit_plan_page.php';
	}

	public function display_settings_nav_tabs( $container = 'h2', $container_class = 'nav-tab-wrapper' ) {
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'stripe';

		$nav_tab_items = $this->get_settings_nav_tab_items();

		$class = ' class="' . esc_attr( $container_class ) . '"';

		$html = '<' . $container . $class . '>';
		foreach ( $nav_tab_items as $nav_tab_item ) {
			$nav_item_class = 'nav-tab';
			if ( $nav_tab_item['tab'] == $active_tab ) {
				$nav_item_class .= ' nav-tab-active';
			}
			$href = add_query_arg( array(
				'page' => 'fullstripe-settings',
				'tab'  => $nav_tab_item['tab']
			), admin_url( 'admin.php' ) );
			$html .= sprintf( $this->settings_nav_tab_item_wrapper, esc_attr( $href ), esc_attr( $nav_item_class ), esc_html( $nav_tab_item['caption'] ) );
		}
		$html .= '</' . $container . '>';

		echo $html;
	}

	private function get_settings_nav_tab_items() {
		$item_stripe         = array(
			'tab'     => 'stripe',
			'caption' => __( 'Stripe', 'wp-full-stripe' ),
			'content' => WP_FULL_STRIPE_DIR . '/pages/fragments/settings_tab_stripe.php'
		);
		$item_appearance     = array(
			'tab'     => 'appearance',
			'caption' => __( 'Appearance', 'wp-full-stripe' ),
			'content' => WP_FULL_STRIPE_DIR . '/pages/fragments/settings_tab_appearance.php'
		);
		$item_email_receipts = array(
			'tab'     => 'email-receipts',
			'caption' => __( 'Email Notifications', 'wp-full-stripe' ),
			'content' => WP_FULL_STRIPE_DIR . '/pages/fragments/settings_tab_email-receipts.php'
		);
		$item_users          = array(
			'tab'     => 'users',
			'caption' => __( 'Users', 'wp-full-stripe' ),
			'content' => WP_FULL_STRIPE_DIR . '/pages/fragments/settings_tab_users.php'
		);

		$nav_tab_items = array();

		array_push( $nav_tab_items, $item_stripe );
		array_push( $nav_tab_items, $item_appearance );
		array_push( $nav_tab_items, $item_email_receipts );
		array_push( $nav_tab_items, $item_users );

		return apply_filters( 'fullstripe_settings_nav_tab_items', $nav_tab_items );
	}

	public function display_settings_active_tab() {

		$selected_tab = $this->get_selected_nav_tab_item();

		if ( isset( $selected_tab ) && isset( $selected_tab['content'] ) ) {
			$tab_content = $selected_tab['content'];
			if ( file_exists( $tab_content ) ) {
				ob_start();
				/** @noinspection PhpIncludeInspection */
				include( $tab_content );
				$content = ob_get_clean();
			} else {
				$content = '<p>' . sprintf( __( 'The selected tab content cannot be displayed: %s', 'wp-full-stripe' ), $tab_content ) . '</p>';
			}
		} else {
			$content = '<p>' . __( 'Invalid tab content.', 'wp-full-stripe' ) . '</p>';
		}

		echo $content;
	}

	private function get_selected_nav_tab_item() {
		$active_tab    = isset( $_GET['tab'] ) ? $_GET['tab'] : 'stripe';
		$nav_tab_items = $this->get_settings_nav_tab_items();
		$selected_item = null;
		foreach ( $nav_tab_items as $nav_tab_item ) {
			if ( $nav_tab_item['tab'] == $active_tab ) {
				$selected_item = $nav_tab_item;
			}
		}

		return $selected_item;
	}

}


