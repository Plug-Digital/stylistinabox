<?php

class WPFS_Base_Table extends WP_List_Table {

	const HTTPS_DASHBOARD_STRIPE_COM = "https://dashboard.stripe.com/";
	const PATH_TEST = "test/";
	const PATH_CUSTOMERS = 'customers/';
	const PATH_CHARGES = 'charges/';
	const PATH_PAYMENTS = 'payments/';
    const PATH_SUBSCRIPTIONS = 'subscriptions/';

	/**
	 * @param $title
	 *
	 * @param $aggregated_columns
	 *
	 * @return string
	 */
	protected function format_column_header_title( $title, array $aggregated_columns = null ) {
		$column_label = "<b>{$title}</b>";
		if ( ! empty( $aggregated_columns ) ) {
			$size = sizeof( $aggregated_columns );
			$column_label .= '<br>';
			foreach ( $aggregated_columns as $key => $value ) {
				$column_label .= $value;
				if ( $key < $size - 1 ) {
					$column_label .= ' / ';
				}
			}
		}

		return $column_label;
	}

	/**
	 * @param $stripe_customer_id
	 * @param $live_mode
	 *
	 * @return string
	 */
	protected function build_stripe_customer_link( $stripe_customer_id, $live_mode ) {
		$href = $this->build_stripe_base_url( $live_mode );
		$href .= self::PATH_CUSTOMERS . $stripe_customer_id;

		return $href;
	}

    /**
     * @param $stripe_customer_id
     * @param $live_mode
     *
     * @return string
     */
    protected function build_stripe_subscription_link( $stripe_subscription_id, $live_mode ) {
        $href = $this->build_stripe_base_url( $live_mode );
        $href .= self::PATH_SUBSCRIPTIONS . $stripe_subscription_id;

        return $href;
    }

    /**
	 * @param $live_mode
	 *
	 * @return string
	 */
	protected function build_stripe_base_url( $live_mode ) {
		$href = self::HTTPS_DASHBOARD_STRIPE_COM;
		if ( $live_mode == 0 ) {
			$href .= self::PATH_TEST;
		}

		return $href;
	}

	/**
	 * @deprecated
	 * @param $stripe_charge_id
	 * @param $live_mode
	 *
	 * @return string
	 */
	protected function build_stripe_charge_link( $stripe_charge_id, $live_mode ) {
		$href = $this->build_stripe_base_url( $live_mode );
		$href .= self::PATH_CHARGES . $stripe_charge_id;

		return $href;
	}

	/**
	 * @param $stripe_charge_id
	 * @param $live_mode
	 *
	 * @return string
	 */
	protected function build_stripe_payments_link( $stripe_charge_id, $live_mode ) {
		$href = $this->build_stripe_base_url( $live_mode );
		$href .= self::PATH_PAYMENTS . $stripe_charge_id;

		return $href;
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 *
	 * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	protected function extra_tablenav( $which ) {
		if ( $which == "top" ) {
			echo '<div class="wrap">';
		}
		if ( $which == "bottom" ) {
			echo '</div>';
		}
	}

}

/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.02.18.
 * Time: 9:17
 */
class WPFS_Multiple_Subscribers_Table extends WPFS_Base_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Subscriber', 'wp-full-stripe' ),
			'plural'   => __( 'Subscribers', 'wp-full-stripe' ),
			'ajax'     => false
		) );
	}

	public function no_items() {
		_e( 'No subscriptions found.', 'wp-full-stripe' );
	}

	public function prepare_items() {
		global $wpdb;

		$query = "SELECT subscriberID,stripeCustomerID,stripeSubscriptionID,chargeMaximumCount,chargeCurrentCount,status,name,email,planID,addressLine1,addressLine2,addressCity,addressState,addressZip,addressCountry,created,cancelled,livemode FROM {$wpdb->prefix}fullstripe_subscribers";

		$where_statement = null;

		$subscriber   = ! empty( $_REQUEST["subscriber"] ) ? esc_sql( trim( $_REQUEST["subscriber"] ) ) : null;
		$subscription = ! empty( $_REQUEST["subscription"] ) ? esc_sql( trim( $_REQUEST["subscription"] ) ) : null;
		$mode         = ! empty( $_REQUEST["mode"] ) ? esc_sql( trim( $_REQUEST["mode"] ) ) : null;

		if ( isset( $subscriber ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( "(LOWER(name) LIKE LOWER('%s') OR LOWER(email) LIKE LOWER('%s') OR stripeCustomerID LIKE '%s')", "%$subscriber%", "%$subscriber%", "%$subscriber%" );
		}

		if ( isset( $subscription ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( "(stripeSubscriptionID LIKE '%s')", "%$subscription%" );
		}

		if ( isset( $mode ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( '(livemode = %d)', $mode == 'live' ? 1 : 0 );
		}

		if ( isset( $where_statement ) ) {
			$query .= $where_statement;
		}

		$order_by = ! empty( $_REQUEST["orderby"] ) ? esc_sql( $_REQUEST["orderby"] ) : 'created';
		$order    = ! empty( $_REQUEST["order"] ) ? esc_sql( $_REQUEST["order"] ) : ( empty( $_REQUEST['orderby'] ) ? 'DESC' : 'ASC' );
		if ( ! empty( $order_by ) && ! empty( $order ) ) {
			$query .= ' ORDER BY ' . $order_by . ' ' . $order;
		}

		$total_items = $wpdb->query( $query );
		$per_page    = 10;
		$total_pages = ceil( $total_items / $per_page );
		$this->set_pagination_args( array(
			"total_items" => $total_items,
			"total_pages" => $total_pages,
			"per_page"    => $per_page,
		) );
		$current_page = $this->get_pagenum();
		if ( ! empty( $current_page ) && ! empty( $per_page ) ) {
			$offset = ( $current_page - 1 ) * $per_page;
			$query .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
		}

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $wpdb->get_results( $query );
	}

	public function get_columns() {
		return array(
			'action'              => __( 'Actions', 'wp-full-stripe' ),
			'subscriber'          => $this->format_column_header_title( __( 'Subscriber', 'wp-full-stripe' ), array(
				__( 'Name', 'wp-full-stripe' ),
				__( 'E-mail', 'wp-full-stripe' )
			) ),
			'subscription_plan'   => $this->format_column_header_title( __( 'Subscription', 'wp-full-stripe' ), array(
				__( 'Plan', 'wp-full-stripe' ),
				__( 'ID', 'wp-full-stripe' )
			) ),
			'subscription_status' => $this->format_column_header_title( __( 'Subscription', 'wp-full-stripe' ), array(
				__( 'Status', 'wp-full-stripe' ),
				__( 'Mode', 'wp-full-stripe' )
			) ),
			'created'             => __( 'Created at', 'wp-full-stripe' )
		);
	}

	protected function get_sortable_columns() {
		return array(
			'created' => array( 'created', false )
		);
	}

	public function display_rows() {
		$items = $this->items;

		list( $columns, $hidden ) = $this->get_column_info();

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$row = '';
				$row .= "<tr id=\"record_{$item->subscriberID}\">";
				foreach ( $columns as $column_name => $column_display_name ) {
					$class = "class=\"$column_name column-$column_name\"";
					$style = "";
					if ( in_array( $column_name, $hidden ) ) {
						$style = "style=\"display:none;\"";
					}
					$attributes = "{$class} {$style}";

					switch ( $column_name ) {
						case "subscriber":
							$href                 = $this->build_stripe_customer_link( $item->stripeCustomerID, $item->livemode );
							$stripe_customer_link = "<a href=\"{$href}\" target=\"_blank\">{$item->email}</a>";
							$name                 = $item->name;
							if ( ! empty( $name ) ) {
								$name_label = stripslashes( $name );
							} else {
								$name_label = __( '&lt;Not specified&gt;', 'wp-full-stripe' );
							}
							$row .= "<td {$attributes}><b>{$name_label}</b><br/>{$stripe_customer_link}</td>";
							break;
						case "subscription_plan":
							$href                   = $this->build_stripe_subscription_link( $item->stripeSubscriptionID, $item->livemode );
							$stripeSubscriptionLink = "<a href=\"{$href}\" target=\"_blank\">{$item->stripeSubscriptionID}</a>";
							$row .= "<td {$attributes}><b>{$item->planID}</b><br/>{$stripeSubscriptionLink}</td>";
							break;
						case "subscription_status":
							$status_Label = ucfirst( $item->status );
							if ( $item->chargeMaximumCount > 0 ) {
								$status_Label = sprintf( "%s (%d/%d)", ucfirst( $item->status ), $item->chargeCurrentCount, $item->chargeMaximumCount );
							}
							$live_mode_label = $item->livemode == 0 ? __( 'Test', 'wp-full-stripe' ) : __( 'Live', 'wp-full-stripe' );
							$row .= "<td {$attributes}><b>{$status_Label}</b><br/>$live_mode_label</td>";
							break;
						case "created":
							$row .= "<td {$attributes}>" . date( 'F jS Y H:i', strtotime( $item->created ) ) . "</td>";
							break;
						case "action":
							$row .= "<td {$attributes}>";
							if ( $item->status == 'cancelled' || $item->status == 'ended' ) {
								$row .= "<button class=\"button delete\" data-id=\"{$item->subscriberID}\" data-type=\"subscription_record\" title=\"" . __( 'Delete', 'wp-full-stripe' ) . "\" data-confirm=\"true\"><i class=\"fa fa-trash-o fa-fw\"></i></button>";
							} else {
								$row .= "<button class=\"button delete\" data-id=\"{$item->subscriberID}\" data-type=\"subscriber\" title=\"" . __( 'Cancel', 'wp-full-stripe' ) . "\"><i class=\"fa fa-ban fa-fw\"></i></button>";
							}
							$row .= '</td>';
							break;
					}
				}

				$row .= "</tr>";

				echo $row;
			}
		}
	}

	protected function get_table_classes() {
		$table_classes = parent::get_table_classes();

		return array_diff( $table_classes, array( 'fixed' ) );
	}

}

/**
 * Created by PhpStorm.
 * User: tnagy
 * Date: 2016.09.01.
 * Time: 12:54
 */
class WPFS_Named_Payments_Table extends WPFS_Base_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Payment', 'wp-full-stripe' ),
			'plural'   => __( 'Payments', 'wp-full-stripe' ),
			'ajax'     => false
		) );
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	public function prepare_items() {
		global $wpdb;

		$query = "SELECT paymentID,eventID,description,paid,livemode,currency,amount,fee,addressLine1,addressLine2,addressCity,addressState,addressZip,addressCountry,created,stripeCustomerID,name,email,formId,formType FROM {$wpdb->prefix}fullstripe_payments";

		$where_statement = null;

		$customer = ! empty( $_REQUEST["customer"] ) ? esc_sql( trim( $_REQUEST["customer"] ) ) : null;
		$payment  = ! empty( $_REQUEST["payment"] ) ? esc_sql( trim( $_REQUEST["payment"] ) ) : null;
		$mode     = ! empty( $_REQUEST["mode"] ) ? esc_sql( trim( $_REQUEST["mode"] ) ) : null;

		if ( isset( $customer ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( "(LOWER(name) LIKE LOWER('%s') OR LOWER(email) LIKE LOWER('%s') OR stripeCustomerID LIKE '%s')", "%$customer%", "%$customer%", "%$customer%" );
		}

		if ( isset( $payment ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( "(eventID LIKE '%s')", "%$payment%" );
		}

		if ( isset( $mode ) ) {
			if ( ! isset( $where_statement ) ) {
				$where_statement = ' WHERE ';
			} else {
				$where_statement .= ' AND ';
			}
			$where_statement .= sprintf( '(livemode = %d)', $mode == 'live' ? 1 : 0 );
		}

		if ( isset( $where_statement ) ) {
			$query .= $where_statement;
		}

		$order_by = ! empty( $_REQUEST["orderby"] ) ? esc_sql( $_REQUEST["orderby"] ) : 'created';
		$order    = ! empty( $_REQUEST["order"] ) ? esc_sql( $_REQUEST["order"] ) : ( empty( $_REQUEST['orderby'] ) ? 'DESC' : 'ASC' );
		if ( ! empty( $order_by ) && ! empty( $order ) ) {
			$query .= ' ORDER BY ' . $order_by . ' ' . $order;
		}

		$total_items = $wpdb->query( $query );
		$per_page    = 10;
		$total_pages = ceil( $total_items / $per_page );
		$this->set_pagination_args( array(
			"total_items" => $total_items,
			"total_pages" => $total_pages,
			"per_page"    => $per_page,
		) );
		$current_page = $this->get_pagenum();
		if ( ! empty( $current_page ) && ! empty( $per_page ) ) {
			$offset = ( $current_page - 1 ) * $per_page;
			$query .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
		}

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $wpdb->get_results( $query );
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		return array(
			'action'         => __( 'Actions', 'wp-full-stripe' ),
			'customer'       => $this->format_column_header_title( __( 'Customer', 'wp-full-stripe' ), array(
				__( 'Name', 'wp-full-stripe' ),
				__( 'E-mail', 'wp-full-stripe' )
			) ),
			'payment'        => $this->format_column_header_title( __( 'Payment', 'wp-full-stripe' ), array(
				__( 'Amount', 'wp-full-stripe' ),
				__( 'ID', 'wp-full-stripe' )
			) ),
			'payment_status' => $this->format_column_header_title( __( 'Status', 'wp-full-stripe' ), array(
				__( 'Paid', 'wp-full-stripe' ),
				__( 'Mode', 'wp-full-stripe' )
			) ),
			'created'        => __( 'Date', 'wp-full-stripe' )
		);
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	protected function get_sortable_columns() {
		return array(
			'created' => array( 'created', false )
		);
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	public function display_rows() {
		$items = $this->items;

		list( $columns, $hidden ) = $this->get_column_info();

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$row = '';
				$row .= "<tr id=\"record_{$item->paymentID}\">";
				foreach ( $columns as $column_name => $column_display_name ) {
					$class = "class=\"$column_name column-$column_name\"";
					$style = "";
					if ( in_array( $column_name, $hidden ) ) {
						$style = " style=\"display:none;\"";
					}
					$attributes = "{$class} {$style}";

					switch ( $column_name ) {
						case "customer":
							$href                 = $this->build_stripe_customer_link( $item->stripeCustomerID, $item->livemode );
							$stripe_customer_link = "<a href=\"{$href}\" target=\"_blank\">{$item->email}</a>";
							$name                 = $item->name;
							if ( ! empty( $name ) ) {
								$name_label = stripslashes( $name );
							} else {
								$name_label = __( '&lt;Not specified&gt;', 'wp-full-stripe' );
							}
							$row .= "<td {$attributes}><b>{$name_label}</b><br/>{$stripe_customer_link}</td>";
							break;
						case "payment":
							$href               = $this->build_stripe_payments_link( $item->eventID, $item->livemode );
							$stripe_charge_link = "<a href=\"{$href}\" target=\"_blank\">{$item->eventID}</a>";
							$amount_label       = MM_WPFS::format_amount_with_currency( $item->currency, $item->amount );
							$row .= "<td {$attributes}><b>{$amount_label}</b><br/>{$stripe_charge_link}</td>";
							break;
						case "payment_status":
							$is_paid_label   = $item->paid == 1 ? __( 'Paid', 'wp-full-stripe' ) : __( 'Not Paid', 'wp-full-stripe' );
							$live_mode_label = $item->livemode == 0 ? __( 'Test', 'wp-full-stripe' ) : __( 'Live', 'wp-full-stripe' );
							$row .= "<td $attributes><b>$is_paid_label</b><br/>$live_mode_label</td>";
							break;
						case "created":
							$row .= "<td {$attributes}>" . date( 'F jS Y H:i', strtotime( $item->created ) ) . "</td>";
							break;
						case "action":
							$row .= "<td {$attributes}><button class=\"button delete\" data-id=\"{$item->paymentID}\" data-type=\"payment\" title=\"" . __( 'Delete (local)', 'wp-full-stripe' ) . "\"><i class=\"fa fa-trash-o fa-fw\"></i></button></td>";
							break;
					}

				}

				$row .= '</tr>';

				echo $row;
			}
		}
	}

	public function no_items() {
		_e( 'No payments found.', 'wp-full-stripe' );
	}

	private function format_address(
		$rec
	) {
		if ( $rec->addressLine1 == "" ) {
			return "";
		}

		$address = $rec->addressLine1 . ( $rec->addressLine2 == "" ? "" : ", $rec->addressLine2" );
		$address .= $rec->addressCity == "" ? "" : ", $rec->addressCity";
		$address .= $rec->addressState == "" ? "" : ", $rec->addressState";
		$address .= $rec->addressZip == "" ? "" : ", $rec->addressZip";
		$address .= $rec->addressCountry == "" ? "" : ", $rec->addressCountry";

		return $address;

	}

}
