<?php

if ( ! class_exists( 'Tribe__Tickets__Tickets' ) ) {
	/**
	 * Class with the API definition and common functionality
	 * for Tribe Tickets Pro. Providers for this functionality need to
	 * extend this class. For a functional example of how this works
	 * see Tribe WooTickets.
	 *
	 * The relationship between orders, attendees and event posts is
	 * maintained through post meta fields set for the attendee object.
	 * Implementing classes are expected to provide the following class
	 * constants detailing those meta keys:
	 *
	 *     ATTENDEE_ORDER_KEY
	 *     ATTENDEE_EVENT_KEY
	 *     ATTENDEE_PRODUCT_KEY
	 *
	 * The post type name used for the attendee object should also be
	 * made available via:
	 *
	 *     ATTENDEE_OBJECT
	 *
	 *
	 * @since  4.5.0.1 Due to a fatal between Event Ticket Plus extending commerces and this class,
	 *                 we changed this from an Abstract to a normal parent class.
	 */
	class Tribe__Tickets__Tickets {

		/**
		 * Flag used to track if the registration form link has been displayed or not.
		 *
		 * @var boolean
		 */
		private static $have_displayed_reg_link = false;

		/**
		 * Function that is used to store the cache of a specific post associated with a set of tickets, where %d is the
		 * ID of the post being affected.
		 *
		 * @since 4.7.1
		 *
		 * @var string
		 */
		private static $cache_key_prefix = 'tribe_event_tickets_from_';

		/**
		 * All Tribe__Tickets__Tickets api consumers. It's static, so it's shared across all children.
		 *
		 * @var array
		 */
		protected static $active_modules = [];

		/**
		 * Default Tribe__Tickets__Tickets ecommerce module.
		 * It's static, so it's shared across all children.
		 *
		 * @var string
		 */
		protected static $default_module = 'Tribe__Tickets__RSVP';

		/**
		 * Indicates if the frontend ticket form script has already been enqueued (or not).
		 *
		 * @var bool
		 */
		protected static $frontend_script_enqueued = false;

		/**
		 * Collection of ticket objects for which we wish to make global stock data available
		 * on the frontend.
		 *
		 * @var array
		 */
		protected static $frontend_ticket_data = [];

		/**
		 * Name of this class. Note that it refers to the child class.
		 *
		 * @var string
		 */
		public $class_name;

		/**
		 * Path of the parent class
		 *
		 * @var string
		 */
		private $parent_path;

		/**
		 * URL of the parent class
		 *
		 * @var string
		 */
		private $parent_url;

		/**
		 * Records batches of tickets that are currently unavailable (used for
		 * displaying the correct "tickets are unavailable" message).
		 *
		 * @var array
		 */
		protected static $currently_unavailable_tickets = [];

		/**
		 * Records posts for which tickets *are* available (used to determine if
		 * a "tickets are unavailable" message should even display).
		 *
		 * @var array
		 */
		protected static $posts_with_available_tickets = [];

		// start API Definitions
		// Child classes must implement all these functions / properties

		/**
		 * Name of the provider
		 *
		 * @var string
		 */
		public $plugin_name;

		/**
		 * Path of the child class
		 *
		 * @var string
		 */
		protected $plugin_path;

		/**
		 * URL of the child class
		 *
		 * @var string
		 */
		protected $plugin_url;

		/**
		 * The name of the post type representing a ticket.
		 *
		 * @var string
		 */
		public $ticket_object = '';

		/* Deprecated vars */

		/**
		 * Name of this class. Note that it refers to the child class.
		 * deprecated - use $class_name
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		public $className;

		/**
		 * Path of the parent class
		 * deprecated - use $parent_path
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		private $parentPath;

		/**
		 * URL of the parent class
		 * deprecated - use $parent_url
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		private $parentUrl;

		/**
		 * Name of the provider
		 * deprecated - use $plugin_name
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		public $pluginName;

		/**
		 * Path of the child class
		 * deprecated - use $plugin_path
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		protected $pluginPath;

		/**
		 * URL of the child class
		 * deprecated - use $plugin_url
		 *
		 * @deprecated 4.6
		 *
		 * @var string
		 */
		protected $pluginUrl;

		/**
		 * Constant with the Transient Key for Attendees Cache
		 */
		const ATTENDEES_CACHE = 'tribe_attendees';

		/**
		 * Meta key that contains the user id
		 *
		 * @deprecated 4.7 Use the $attendee_user_id variable instead
		 *
		 * @var string
		 */
		const ATTENDEE_USER_ID = '_tribe_tickets_attendee_user_id';

		/**
		 * Meta key that contains the user id
		 *
		 * @var string
		 */
		public $attendee_user_id = '_tribe_tickets_attendee_user_id';

		/**
		 * Name of the CPT that holds Orders
		 */
		public $order_object = '';

		/**
		 * Name of the CPT that holds Attendees.
		 *
		 * @var string
		 */
		public $attendee_object = '';

		/**
		 * Meta key that relates Attendees and Events.
		 *
		 * @var string
		 */
		public $attendee_event_key = '';

		/**
		 * Meta key that relates Attendees and Products.
		 *
		 * @var string
		 */
		public $attendee_product_key = '';

		/**
		 * Indicates if a ticket for this attendee was sent out via email.
		 *
		 * @var boolean
		 */
		public $attendee_ticket_sent = '';

		/**
		 * Meta key that if this attendee wants to show on the attendee list
		 *
		 * @var string
		 */
		public $attendee_optout_key = '';

		/**
		 * The provider used for Attendees and Tickets ORM.
		 *
		 * @var string
		 */
		public $orm_provider = 'default';

		/**
		 * Returns link to the report interface for sales for an event or
		 * null if the provider doesn't have reporting capabilities.
		 *
		 * @abstract
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return mixed
		 */
		public function get_event_reports_link( $post_id ) {}

		/**
		 * Returns link to the report interface for sales for a single ticket or
		 * null if the provider doesn't have reporting capabilities.
		 * As of 4.6 we reversed the params and deprecated $post_id as it was never used
		 *
		 * @abstract
		 *
		 * @param deprecated $post_id ID of parent "event" post
		 * @param int $ticket_id ID of ticket post
		 * @return mixed
		 */
		public function get_ticket_reports_link( $post_id_deprecated, $ticket_id ) {}

		/**
		 * Returns a single ticket
		 *
		 * @abstract
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param int $ticket_id ID of ticket post
		 * @return mixed
		 */
		public function get_ticket( $post_id, $ticket_id ) {}

		/**
		 * Retrieve the Query args to fetch all the Tickets.
		 *
		 * @since  4.6
		 *
		 * @param  int|WP_Post $post_id Build the args to query only
		 *                           for tickets related to this post ID.
		 *
		 * @return array
		 */
		public function get_tickets_query_args( $post_id = null ) {
			if ( $post_id instanceof WP_Post ) {
				$post_id = $post_id->ID;
			}

			$args = array(
				'post_type'      => array( $this->ticket_object ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			);

			if ( ! empty( $post_id ) ) {
				$args['meta_query'] = array(
					array(
						'key'     => $this->event_key,
						'value'   => $post_id,
						'compare' => '=',
					),
				);
			}

			/**
			 * Filters the query arguments that will be used to fetch tickets.
			 *
			 * @since 4.8
			 *
			 * @param array $args
			 */
			$args = apply_filters( 'tribe_tickets_get_tickets_query_args', $args );

			return $args;
		}

		/**
		 * Retrieve the ID numbers of all tickets assigned to an event.
		 *
		 * @since  4.6
		 *
		 * @param  int|WP_Post $post Only get tickets assigned to this post ID.
		 *
		 * @return array
		 */
		public function get_tickets_ids( $post = null ) {
			if ( ! empty( $post ) ) {
				if ( ! $post instanceof WP_Post ) {
					$post = get_post( $post );
				}
				if ( ! $post instanceof WP_Post ) {
					return false;
				}
				$args = $this->get_tickets_query_args( $post->ID );
			} else {
				$args = $this->get_tickets_query_args();
			}

			$cache = new Tribe__Cache();
			$cache_key = $cache->make_key( $args );
			$query = $cache->get( $cache_key );

			if ( $query instanceof WP_Query ) {
				return $query->posts;
			}

			$query = new WP_Query( $args );
			$cache->set( $cache_key, $query, Tribe__Cache::NO_EXPIRATION );

			return $query->posts;
		}

		/**
		 * Returns the html for the delete ticket link
		 *
		 * @since 4.6
		 *
		 * @param object $ticket Ticket object
		 * @return string HTMl link
		 */
		public function get_ticket_delete_link( $ticket = null ) {
			if ( empty( $ticket ) ) {
				return;
			}

			$button_text = ( 'Tribe__Tickets__RSVP' === $ticket->provider_class ) ? __( 'Delete RSVP', 'event-tickets' ) : __( 'Delete Ticket', 'event-tickets' ) ;

			/**
			 * Allows for the filtering and testing if a user can delete tickets
			 *
			 * @since 4.6
			 *
			 * @param bool true
			 * @param int ticket post ID
			 * @return string HTML link | void HTML link
			 */
			if ( apply_filters( 'tribe_tickets_current_user_can_delete_ticket', true, $ticket->ID, $ticket->provider_class ) ) {
				$delete_link = sprintf(
					'<span><a href="#" attr-provider="%1$s" attr-ticket-id="%2$s" id="ticket_delete_%2$s" class="ticket_delete">%3$s</a></span>',
					$ticket->provider_class,
					$ticket->ID,
					esc_html( $button_text )
				);

				return $delete_link;
			}

			$delete_link = sprintf(
				'<span><a href="#" attr-provider="%1$s" attr-ticket-id="%2$s" id="ticket_delete_%2$s" class="ticket_delete">%3$s</a></span>',
				$ticket->provider_class,
				$ticket->ID,
				esc_html__( $button_text )
			);

			return $delete_link;
		}

		/**
		 * Returns the url for the move ticket link
		 *
		 * @since 4.6
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param object $ticket Ticket object
		 * @return string HTML link | void HTML link
		 */
		public function get_ticket_move_url( $post_id, $ticket = null ) {
			if ( empty( $ticket ) || empty( $post_id ) ) {
				return;
			}

			$post_url = get_edit_post_link( $post_id, 'admin' );

			$move_type_url = add_query_arg(
				array(
					'dialog'         => Tribe__Tickets__Main::instance()->move_ticket_types()->dialog_name(),
					'ticket_type_id' => $ticket->ID,
					'check'          => wp_create_nonce( 'move_tickets' ),
					'TB_iframe'      => 'true',
				),
				$post_url
			);

			return $move_type_url;
		}

		/**
		 * Returns the html for the move ticket link
		 *
		 * @since 4.6
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param object $ticket Ticket object
		 * @return string HTML link | void HTML link
		 */
		public function get_ticket_move_link( $post_id, $ticket = null ) {
			if ( empty( $ticket ) ) {
				return;
			}

			$button_text = ( 'Tribe__Tickets__RSVP' === $ticket->provider_class ) ? __( 'Move RSVP', 'event-tickets' ) : __( 'Move Ticket', 'event-tickets' ) ;

			$move_url = $this->get_ticket_move_url( $post_id, $ticket );

			if ( empty( $move_url ) ) {
				return;
			}

			// Make sure Thickbox is available regardless of which admin page we're on.
			add_thickbox();

			$move_link = sprintf( '<a href="%1$s" class="thickbox tribe-ticket-move-link">' . esc_html( $button_text ) . '</a>', $move_url );

			return $move_link;
		}

		/**
		 * Get the controls (move, delete) as a string and add to our ajax return
		 *
		 * @deprecated 4.6.2
		 * @since 4.6
		 *
		 * @param array $return the ajax return data
		 * @return array $return modified data
		 */
		public function ajax_ticket_edit_controls( $return ) {
			$ticket = $this->get_ticket( $return['post_id'], $return['ID'] );

			if ( empty( $ticket ) ) {
				return $return;
			}

			$controls   = [];

			if ( tribe_is_truthy( tribe_get_request_var( 'is_admin' ) ) ) {
				$controls[] = $this->get_ticket_move_link( $return['post_id'], $ticket );
			}
			$controls[] = $this->get_ticket_delete_link( $ticket );

			if ( ! empty( $controls ) ) {
				$return['controls'] = join( '  |  ', $controls );
			}

			return $return;
		}

		/**
		 * Attempts to load the specified ticket type post object.
		 *
		 * @param int $ticket_id ID of ticket post
		 * @return Tribe__Tickets__Ticket_Object|null
		 */
		public static function load_ticket_object( $ticket_id ) {
			foreach ( self::modules() as $provider_class => $name ) {
				$provider = call_user_func( array( $provider_class, 'get_instance' ) );
				$event    = $provider->get_event_for_ticket( $ticket_id );

				if ( ! $event ) {
					continue;
				}

				$ticket_object = $provider->get_ticket( $event->ID, $ticket_id );

				if ( $ticket_object ) {
					return $ticket_object;
				}
			}

			return null;
		}

		/**
		 * Returns the event post corresponding to the possible ticket object/ticket ID.
		 *
		 * This is used to help differentiate between products which act as tickets for an
		 * event and those which do not. If $possible_ticket is not related to any events
		 * then boolean false will be returned.
		 *
		 * This stub method should be treated as if it were an abstract method - ie, the
		 * concrete class ought to provide the implementation.
		 *
		 * @param $possible_ticket
		 *
		 * @return bool|WP_Post
		 */
		public function get_event_for_ticket( $ticket_product ) {
			if ( is_object( $ticket_product ) && isset( $ticket_product->ID ) ) {
				$ticket_product = $ticket_product->ID;
			}

			if ( null === ( $product = get_post( $ticket_product ) ) ) {
				return false;
			}

			$event_id = get_post_meta( $ticket_product, $this->event_key, true );

			if ( ! $event_id && '' === ( $event_id = get_post_meta( $ticket_product, $this->attendee_event_key, true ) ) ) {
				return false;
			}

			if ( in_array( get_post_type( $event_id ), Tribe__Tickets__Main::instance()->post_types() ) ) {
				return get_post( $event_id );
			}

			return false;
		}

		/**
		 * Deletes a ticket
		 *
		 * @abstract
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param int $ticket_id ID of ticket post
		 * @return mixed
		 */
		public function delete_ticket( $post_id, $ticket_id ) {}

		/**
		 * Saves a ticket
		 *
		 * @abstract
		 *
		 * @param int   $post_id
		 * @param int   $ticket
		 * @param array $raw_data
		 * @return mixed
		 */
		public function save_ticket( $post_id, $ticket, $raw_data = [] ) {}

		/**
		 * Returns all the tickets for an event.
		 *
		 * @abstract
		 *
		 * @param int $post_id ID of parent "event" post.
		 *
		 * @return Tribe__Tickets__Ticket_Object[] List of ticket objects.
		 */
		protected function get_tickets( $post_id ) {}

		/**
		 * Get attendees for a Post ID / Post type.
		 *
		 * @param int         $post_id   Post ID.
		 * @param null|string $post_type Post type.
		 *
		 * @return array List of attendees.
		 */
		public function get_attendees_by_id( $post_id, $post_type = null ) {
			return $this->get_attendees_by_post_id( $post_id );
		}

		/**
		 * Get attendees for an event ID.
		 *
		 * @param int $event_id Event post ID.
		 *
		 * @return array List of attendees.
		 */
		protected function get_attendees_by_post_id( $event_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			return $this->get_attendees_from_module( $repository->by( 'event', $event_id )->all(), $event_id );
		}

		/**
		 * Get attendees for a ticket ID.
		 *
		 * @since 4.10.6
		 *
		 * @param int $ticket_id Ticket ID.
		 *
		 * @return array List of attendees.
		 */
		protected function get_attendees_by_ticket_id( $ticket_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			return $this->get_attendees_from_module( $repository->by( 'ticket', $ticket_id )->all() );
		}

		/**
		 * Get attendees for a ticket ID.
		 *
		 * @since 4.10.6
		 *
		 * @param int $ticket_id Ticket ID.
		 *
		 * @return array List of attendees.
		 */
		protected function get_attendees_by_product_id( $ticket_id ) {
			return $this->get_attendees_by_ticket_id( $ticket_id );
		}

		/**
		 * Get attendees for a ticket by order ID, optionally by ticket ID.
		 *
		 * @since 4.6
		 *
		 * @param int|string $order_id  Order ID.
		 * @param null|int   $ticket_id (optional) Ticket ID.
		 *
		 * @return array List of attendees.
		 */
		protected function get_attendees_by_order_id( $order_id ) {
			$ticket_id = null;

			// Support an optional second argument while not causing warnings from other ticket provider classes.
			if ( 1 < func_num_args() ) {
				$ticket_id = func_get_arg( 1 );
			}

			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			$repository->by( 'order', $order_id );

			if ( $ticket_id ) {
				$repository->by( 'ticket', $ticket_id );
			}

			return $this->get_attendees_from_module( $repository->all() );
		}

		/**
		 * Get attendees for a ticket by attendee ID.
		 *
		 * @since 4.6
		 *
		 * @param int $attendee_id Attendee ID.
		 *
		 * @return array List of attendees.
		 */
		protected function get_attendees_by_attendee_id( $attendee_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			return $this->get_attendees_from_module( $repository->by( 'id', $attendee_id )->all() );
		}

		/**
		 * Get attendees for a ticket by user ID.
		 *
		 * @since 4.10.6
		 *
		 * @param int $user_id User ID.
		 * @param int $post_id Post or Event ID.
		 *
		 * @return array List of attendees.
		 */
		public function get_attendees_by_user_id( $user_id, $post_id = 0 ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			$repository->by( 'user', $user_id );

			if ( $post_id ) {
				$repository->by( 'event', $post_id );
			}

			return $this->get_attendees_from_module( $repository->all() );
		}

		/**
		 * Get All Attendees by ticket/attendee ID
		 *
		 * @since 4.8.0
		 *
		 * @param int $attendee_id
		 * @return array
		 */
		public function get_all_attendees_by_attendee_id( $attendee_id ) {
			return $this->get_attendees_by_attendee_id( $attendee_id );
		}

		/**
		 * Get attendees from provided query
		 *
		 * @param WP_Query $attendees_query
		 * @param int $post_id ID of parent "event" post
		 * @return mixed
		 */
		protected function get_attendees( $attendees_query, $post_id ) {
			$attendees = [];

			foreach ( $attendees_query->posts as $attendee ) {
				$attendee_data = $this->get_attendee( $attendee, $post_id );

				if ( ! $attendee_data ) {
					continue;
				}

				$attendees[] = $attendee_data;
			}

			return $attendees;
		}

		/**
		 * Whether a specific attendee is valid toward inventory decrease or not.
		 *
		 * @since 4.7
		 *
		 * @param array $attendee
		 *
		 * @return bool
		 */
		public function attendee_decreases_inventory( array $attendee ) {
			return true;
		}

		/**
		 * Mark an attendee as checked in
		 *
		 * @abstract
		 *
		 * @param int $attendee_id
		 * @param $qr true if from QR checkin process
		 * @return mixed
		 */
		public function checkin( $attendee_id ) {
			update_post_meta( $attendee_id, $this->checkin_key, 1 );

			$args = func_get_args();
			$qr = null;

			if ( isset( $args[1] ) && $qr = (bool) $args[1] ) {
				update_post_meta( $attendee_id, '_tribe_qr_status', 1 );
			}

			/**
			 * Fires a checkin action
			 *
			 * @since 4.7
			 *
			 * @param int       $attendee_id
			 * @param bool|null $qr
			 */
			do_action( 'event_tickets_checkin', $attendee_id, $qr );

			return true;
		}

		/**
		 * Mark an attendee as not checked in
		 *
		 * @abstract
		 *
		 * @param int $attendee_id
		 * @return mixed
		 */
		public function uncheckin( $attendee_id ) {
			delete_post_meta( $attendee_id, $this->checkin_key );
			delete_post_meta( $attendee_id, '_tribe_qr_status' );

			/**
			 * Fires an uncheckin action
			 *
			 * @since 4.7
			 *
			 * @param int $attendee_id
			 */
			do_action( 'event_tickets_uncheckin', $attendee_id );

			return true;
		}

		/**
		 * Renders the advanced fields in the new/edit ticket form.
		 * Using the method, providers can add as many fields as
		 * they want, specific to their implementation.
		 *
		 * @abstract
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param int $ticket_id ID of ticket post
		 * @return mixed
		 */
		public function do_metabox_capacity_options( $post_id, $ticket_id ) {}

		/**
		 * Renders the front end form for selling tickets in the event single page
		 *
		 * @param $content
		 * @return mixed
		 */
		public function front_end_tickets_form( $content ) {}

		/**
		 * Returns the markup for the price field
		 * (it may contain the user selected currency, etc)
		 *
		 * @param object|int $product
		 * @return string
		 */
		public function get_price_html( $product ) {
			return '';
		}

		/**
		 * Indicates if the module/ticket provider supports a concept of global stock.
		 *
		 * For backward compatibility reasons this method has not been declared abstract but
		 * implementaions are still expected to override it.
		 *
		 * @return bool
		 */
		public function supports_global_stock() {
			return false;
		}

		/**
		 * Returns instance of the child class (singleton)
		 *
		 * @static
		 *
		 * @return mixed
		 */
		public static function get_instance() {}

		// end API Definitions

		/**
		 *
		 */
		public function __construct() {
			// As this is an abstract class, we want to know which child instantiated it
			$this->class_name = $this->className = get_class( $this );

			$this->parent_path = $this->parentPath = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
			$this->parent_url  = $this->parentUrl  = trailingslashit( plugins_url( '', $this->parent_path ) );

			// Register all Tribe__Tickets__Tickets api consumers
			self::$active_modules[ $this->class_name ] = $this->plugin_name;

			add_action( 'wp', array( $this, 'hook' ) );

			/**
			 * Priority set to 11 to force a specific display order
			 *
			 * @since 4.6
			 */
			add_action( 'tribe_events_tickets_metabox_edit_main', array( $this, 'do_metabox_capacity_options' ), 11, 2 );

			// Ensure ticket prices and event costs are linked
			add_filter( 'tribe_events_event_costs', array( $this, 'get_ticket_prices' ), 10, 2 );

			add_action( 'event_tickets_checkin', array( $this, 'purge_attendees_transient' ) );
			add_action( 'event_tickets_uncheckin', array( $this, 'purge_attendees_transient' ) );
			add_action( 'template_redirect', array( $this, 'maybe_redirect_to_attendees_registration_screen' ), 0 );
		}

		/**
		 * Most Commerce Providers needs this to be setup later than when the actual class is actually loaded
		 *
		 * For Frontend Hooks, admin ones need to be loaded earlier
		 *
		 * @since 4.7.5
		 *
		 * @return void
		 */
		public function hook() {
			// Front end
			$ticket_form_hook = $this->get_ticket_form_hook();

			if ( ! empty( $ticket_form_hook ) ) {
				add_action( $ticket_form_hook, array( $this, 'maybe_add_front_end_tickets_form' ), 5 );
				add_filter( $ticket_form_hook, array( $this, 'show_tickets_unavailable_message' ), 6 );
			}

			add_filter( 'the_content', array( $this, 'front_end_tickets_form_in_content' ), 11 );
			add_filter( 'the_content', array( $this, 'show_tickets_unavailable_message_in_content' ), 12 );
			/**
			 * Trigger an action every time a new ticket instance has been created
			 *
			 * @since 4.9
			 *
			 * @param Tribe__Tickets__Tickets $ticket_handler
			 */
			do_action( 'tribe_tickets_tickets_hook', $this );
		}


		/**
		 * Remove the attendees transient when a Ticket change its state
		 *
		 * @since 4.7.4
		 *
		 * @param  int $attendee_id
		 * @return void
		 */
		public function purge_attendees_transient( $attendee_id ) {

			$event_id = $this->get_event_id_from_attendee_id( $attendee_id );

			if ( $event_id ) {
				tribe( 'post-transient' )->delete( $event_id, self::ATTENDEES_CACHE );
			}
		}

		/**
		 * Maybe add the Tickets Form as shouldn't be added if is unchecked from the settings
		 *
		 * @since 4.7.3
		 *
		 * @param string $content
		 */
		public function maybe_add_front_end_tickets_form( $content ) {
			if ( ! tribe_tickets_post_type_enabled( get_post_type() ) ) {
				return;
			}

			if ( post_password_required( get_the_ID() ) ) {
				return;
			}

			return $this->front_end_tickets_form( $content );
		}

		// start Attendees

		/**
		 * Returns all the attendees for an event. Queries all registered providers.
		 *
		 * @static
		 *
		 * @param int   $post_id ID of parent "event" post.
		 * @param array $args    List of arguments to filter by.
		 *
		 * @return array List of attendees.
		 */
		public static function get_event_attendees( $post_id, $args = [] ) {
			$attendees = [];

			/**
			 * Filter to skip all empty $post_ID otherwise will fallback to the current global post ID
			 *
			 * @since 4.9
			 * @since 4.10.6 Added $args parameter.
			 *
			 * @param bool  $skip_empty_post If the empty post should be skipped or not
			 * @param int   $post_id         ID of the post being affected
			 * @param array $args            List of arguments to filter by.
			 */
			$skip_empty_post = apply_filters( 'tribe_tickets_event_attendees_skip_empty_post', true, $post_id, $args );

			/**
			 * Process an attendee only if:
			 *
			 * - $skip_empty_post is true and $post_id is not empty => ( true && false ) => ! false => true
			 * - $skip_empty_post is false and $post_id is empty => ( false && true ) => ! false => true
			 * - $skip_empty_post is false and $post_id is not empty => ( false && false ) => ! false => true
			 *
			 * Is not executed if:
			 *
			 * - $skip_empty_post is true and $post_id is empty => ( true && true ) => ! true => false
			 */
			if ( ! ( $skip_empty_post && empty( $post_id ) ) ) {
				/**
				 * Filters the cache expiration when this function is called from an admin screen.
				 *
				 * Returning a falsy value here will force a fetch each time.
				 *
				 * @since 4.7
				 * @since 4.10.6 Added $args parameter.
				 *
				 * @param int   $admin_expire The cache expiration in seconds; defaults to 2 minutes.
				 * @param int   $post_id      The ID of the post attendees are being fetched for.
				 * @param array $args         List of arguments to filter by.
				 */
				$admin_expire = apply_filters( 'tribe_tickets_attendees_admin_expire', 120, $post_id, $args );

				/**
				 * Filters the cache expiration when this function is called from a non admin screen.
				 *
				 * Returning a falsy value here will force a refetch each time.
				 *
				 * @since 4.7
				 * @since 4.10.6 Added $args parameter.
				 *
				 * @param int   $admin_expire The cache expiration in seconds, defaults to an hour.
				 * @param int   $post_id      The ID of the post attendees are being fetched for.
				 * @param array $args         List of arguments to filter by.
				 */
				$expire = apply_filters( 'tribe_tickets_attendees_expire', HOUR_IN_SECONDS, $post_id, $args );

				$expire = is_admin() ? (int) $admin_expire : (int) $expire;

				$attendees_from_cache = false;

				$post_transient = null;

				$cache_key = false;

				if ( empty( $args ) && 0 < $post_id ) {
					$cache_key = (int) $post_id;
				}

				if ( 0 !== $expire && $cache_key ) {
					/** @var Tribe__Post_Transient $post_transient */
					$post_transient = tribe( 'post-transient' );

					$attendees_from_cache = $post_transient->get( $cache_key, self::ATTENDEES_CACHE );

					// if there is a valid transient, we'll use the value from that and note
					// that we have fetched from cache
					if ( false !== $attendees_from_cache ) {
						$attendees            = empty( $attendees_from_cache ) ? [] : $attendees_from_cache;
						$attendees_from_cache = true;
					}
				}

				// if we haven't grabbed attendees from cache, then attempt to fetch attendees
				if ( false === $attendees_from_cache && empty( $attendees ) ) {
					$attendee_data = self::get_event_attendees_by_args( $post_id, $args );

					if ( ! empty( $attendee_data['attendees'] ) ) {
						$attendees = $attendee_data['attendees'];
					}

					if ( 0 !== $expire && $cache_key ) {
						$post_transient->set( $cache_key, self::ATTENDEES_CACHE, $attendees, $expire );
					}
				}
			}

			/**
			 * Filters the return data for event attendees.
			 *
			 * @since 4.4
			 * @since 4.10.6 Added $args parameter.
			 *
			 * @param array $attendees Array of event attendees.
			 * @param int   $post_id   Event post ID.
			 * @param array $args      List of arguments to filter by.
			 */
			return apply_filters( 'tribe_tickets_event_attendees', $attendees, $post_id, $args );
		}

		/**
		 * Returns all the attendees for an event with filtered by arguments. Queries all registered providers.
		 *
		 * @since 4.10.6
		 *
		 * @static
		 *
		 * @param int   $post_id ID of parent "event" post.
		 * @param array $args {
		 *      List of arguments to filter attendees by.
		 *
		 *      @type boolean $return_total_found Whether to return total_found count in an array along with list of
		 *                                        attendees. Default is off.
		 *      @type int     $page               Page number of attendees to return. Default is page 1.
		 *      @type int     $per_page           How many attendees to return per page. Default is all.
		 *      @type string  $fields             Which fields to return. Default is all.
		 *      @type array   $by                 List of ORM->by() filters to use. [what=>[args...]], [what=>arg], or
		 *                                        [[what,args...]] format.
		 *      @type array   $where_multi        List of ORM->where_multi() filters to use. [[what,args...]] format.
		 * }
		 *
		 * @return array List of attendees and total_found.
		 */
		public static function get_event_attendees_by_args( $post_id, $args = [] ) {
			$attendee_data = [
				'total_found' => 0,
				'attendees'   => [],
			];

			if ( empty( $post_id ) ) {
				return $attendee_data;
			}

			$provider = 'default';

			if ( ! empty( $args['provider'] ) ) {
				$provider = $args['provider'];
			}

			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $provider );

			// Limit by post ID.
			$repository->by( 'event', $post_id );

			self::pass_args_to_repository( $repository, $args );

			if ( ! empty( $args['return_total_found'] ) ) {
				$repository->set_found_rows( true );
			}

			$attendee_posts = $repository->all();

			if ( ! empty( $args['return_total_found'] ) ) {
				$attendee_data['total_found'] = $repository->found();
			}

			$attendee_data['attendees'] = self::get_attendees_from_modules( $attendee_posts, $post_id );

			return $attendee_data;
		}

		/**
		 * Pass arguments to repository object with dynamic support for by() and where_multi().
		 *
		 * @since 4.10.6
		 *
		 * @param Tribe__Repository $repository Repository object.
		 * @param array             $args       {
		 *      List of arguments to filter by.
		 *
		 *      @type int     $page               Page number of results to return. Default is page 1.
		 *      @type int     $per_page           How many results to return per page. Default is all.
		 *      @type string  $fields             Which fields to return. Default is all.
		 *      @type array   $by                 List of ORM->by() filters to use. [what=>[args...]], [what=>arg], or
		 *                                        [[what,args...]] format.
		 *      @type array   $where_multi        List of ORM->where_multi() filters to use. [[what,args...]] format.
		 * }
		 */
		protected static function pass_args_to_repository( $repository, $args ) {
			// Only return specific fields.
			if ( ! empty( $args['fields'] ) ) {
				$repository->fields( $args['fields'] );
			}

			// Handle filtering.
			if ( ! empty( $args['by'] ) ) {
				foreach ( $args['by'] as $by => $by_args ) {
					$by_args = (array) $by_args;

					if ( is_string( $by ) ) {
						array_unshift( $by_args, $by );
					}

					call_user_func_array( [ $repository, 'by' ], $by_args );
				}
			}

			// Handle post__in.
			if ( ! empty( $args['in'] ) ) {
				$repository->in( (array) $args['in'] );
			}

			// Handle post__not_in.
			if ( ! empty( $args['not_in'] ) ) {
				$repository->not_in( (array) $args['not_in'] );
			}

			// Handle multi filtering.
			if ( ! empty( $args['where_multi'] ) ) {
				foreach ( $args['where_multi'] as $where_multi_args ) {
					call_user_func_array( [ $repository, 'where_multi' ], $where_multi_args );
				}
			}

			// Set current page.
			if ( ! empty( $args['page'] ) ) {
				$repository->page( absint( $args['page'] ) );
			}

			// Limit results per page.
			if ( ! empty( $args['per_page'] ) ) {
				$repository->per_page( absint( $args['per_page'] ) );
			}
		}

		/**
		 * Get attendee data for attendees from the associated modules.
		 *
		 * @since 4.10.6
		 *
		 * @param array $attendees Attendee objects or IDs.
		 * @param int   $post_id   Parent post ID.
		 *
		 * @return array The attendee data for attendees.
		 */
		public static function get_attendees_from_modules( $attendees, $post_id = 0 ) {
			$attendees_from_modules = [];

			foreach ( $attendees as $attendee ) {
				/** @var Tribe__Tickets__Tickets $provider */
				$provider = tribe_tickets_get_ticket_provider( $attendee );

				if ( ! $provider ) {
					continue;
				}

				$attendee_data = $provider->get_attendee( $attendee, $post_id );

				if ( ! $attendee_data ) {
					continue;
				}

				// Set the `ticket_exists` flag on attendees if the ticket they are associated with does not exist.
				$attendee_data['ticket_exists'] = ! empty( $attendee_data['product_id'] ) && get_post( $attendee_data['product_id'] );

				$attendees_from_modules[] = $attendee_data;
			}

			return $attendees_from_modules;
		}

		/**
		 * Get attendee data for attendees from the current module.
		 *
		 * @since 4.10.6
		 *
		 * @param array $attendees Attendee objects or IDs.
		 * @param int   $post_id   Parent post ID.
		 *
		 * @return array The attendee data for attendees.
		 */
		public function get_attendees_from_module( $attendees, $post_id = 0 ) {
			$attendees_from_module = [];

			foreach ( $attendees as $attendee ) {
				$attendee_data = $this->get_attendee( $attendee, $post_id );

				if ( ! $attendee_data ) {
					continue;
				}

				// Set the `ticket_exists` flag on attendees if the ticket they are associated with does not exist.
				$attendee_data['ticket_exists'] = ! empty( $attendee_data['product_id'] ) && get_post( $attendee_data['product_id'] );

				$attendees_from_module[] = $attendee_data;
			}

			return $attendees_from_module;
		}

		/**
		 * Get attendee data for attendee.
		 *
		 * @since 4.10.6
		 *
		 * @param WP_Post|int $attendee Attendee object or ID.
		 * @param int         $post_id  Parent post ID.
		 *
		 * @return array|false The attendee data or false if the ticket is invalid.
		 */
		public function get_attendee( $attendee, $post_id = 0 ) {
			return false;
		}

		/**
		 * Returns an array of attendees for the specified event, in relation to
		 * this ticketing provider.
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return array
		 */
		public function get_attendees_array( $post_id ) {
			return $this->get_attendees_by_post_id( $post_id );
		}

		/**
		 * Returns total count of attendees for the specified event, in relation to
		 * this ticketing provider.
		 *
		 * @since 4.10.6
		 *
		 * @param int $post_id ID of parent "event" post
		 *
		 * @return int Total count of attendees.
		 */
		public function get_attendees_count( $post_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			return $repository->by( 'event', $post_id )->found();
		}

		/**
		 * Returns total count of attendees for the specified event, in relation to
		 * this ticketing provider.
		 *
		 * @since 4.10.6
		 *
		 * @param int $post_id ID of parent "event" post.
		 * @param int $user_id ID of user.
		 *
		 * @return int Total count of attendees.
		 */
		public function get_attendees_count_by_user( $post_id, $user_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $this->orm_provider );

			return $repository->by( 'event', $post_id )->by( 'user', $user_id )->found();
		}

		/**
		 * Returns the total number of attendees for an event (regardless of provider).
		 *
		 * @param int   $post_id ID of parent "event" post.
		 * @param array $args    {
		 *      List of arguments to filter attendees by.
		 *
		 *      @type array $by          List of ORM->by() filters to use. [what=>[args...]], [what=>arg], or
		 *                               [[what,args...]] format.
		 *      @type array $where_multi List of ORM->where_multi() filters to use. [[what,args...]] format.
		 * }
		 *
		 * @return int Total count of attendees.
		 */
		public static function get_event_attendees_count( $post_id, $args = [] ) {
			// Post ID is required.
			if ( empty( $post_id ) ) {
				return 0;
			}

			$provider = 'default';

			if ( ! empty( $args['provider'] ) ) {
				$provider = $args['provider'];
			}

			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees( $provider );

			$repository->by( 'event', $post_id );

			self::pass_args_to_repository( $repository, $args );

			return $repository->found();
		}

		/**
		 * Returns all tickets for an event (all providers are queried for this information).
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return array
		 */
		public static function get_all_event_tickets( $post_id ) {

			$cache_key = self::$cache_key_prefix . $post_id;
			$cache = new Tribe__Cache();
			$tickets = $cache->get( $cache_key );

			if ( is_array( $tickets ) ) {
				return $tickets;
			}

			$tickets = [];
			$modules = self::modules();

			foreach ( $modules as $class => $module ) {
				$obj              = call_user_func( array( $class, 'get_instance' ) );
				$provider_tickets = $obj->get_tickets( $post_id );
				if ( is_array( $provider_tickets ) ) {
					$tickets[] = $provider_tickets;
				}
			}

			$tickets = empty( $tickets ) ? [] : call_user_func_array( 'array_merge', $tickets );
			$cache->set( $cache_key, $tickets, Tribe__Cache::NO_EXPIRATION );

			return $tickets;
		}

		/**
		 * Tests to see if the provided object/ID functions as a ticket for the event
		 * and returns the corresponding event if so (or else boolean false).
		 *
		 * All registered providers are asked to perform this test.
		 *
		 * @param object|int $possible_ticket
		 *
		 * @return WP_Post|false
		 */
		public static function find_matching_event( $possible_ticket ) {
			foreach ( self::modules() as $class => $module ) {
				$obj   = call_user_func( array( $class, 'get_instance' ) );
				$event = $obj->get_event_for_ticket( $possible_ticket );
				if ( $event instanceof WP_Post ) {
					return $event;
				}
			}

			return false;
		}

		/**
		 * Returns the sum of all checked-in attendees for an event. Queries all registered providers.
		 *
		 * @static
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return mixed
		 */
		final public static function get_event_checkedin_attendees_count( $post_id ) {
			/** @var Tribe__Tickets__Attendee_Repository $repository */
			$repository = tribe_attendees();

			return $repository->by( 'event', $post_id )->by( 'checkedin', true )->found();
		}

		// end Attendees

		// start Helpers

		/**
		 * Indicates if any of the currently available providers support global stock.
		 *
		 * @return bool
		 */
		public static function global_stock_available() {
			foreach ( self::modules() as $class => $module ) {
				$provider = call_user_func( array( $class, 'get_instance' ) );

				if ( method_exists( $provider, 'supports_global_stock' ) && $provider->supports_global_stock() ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Echos the class for the <tr> in the tickets list admin
		 */
		protected function tr_class() {
			echo 'ticket_advanced_' . sanitize_html_class( $this->class_name );
		}

		/**
		 * Generates a set of radio buttons listing the available global stock mode options.
		 *
		 * @param string (empty string) $current_option
		 * @return string
		 */
		protected function global_stock_mode_selector( $current_option = '' ) {
			$output = "<fieldset id='ticket_global_stock' class='input_block' >";
			$output .= "<legend class='ticket_form_label'>Capacity:</legend>";

			// Default to using own stock unless the user explicitly specifies otherwise (important
			// to avoid assuming global stock mode if global stock is enabled/disabled accidentally etc)
			if ( empty( $current_option ) ) {
				$current_option = Tribe__Tickets__Global_Stock::OWN_STOCK_MODE;
			}

			foreach ( $this->global_stock_mode_options() as $identifier => $name ) {
				$output .= '<label for="' . esc_attr( $identifier ) . '" class="ticket_field"><input type="radio" id="' . esc_attr( $identifier ) . '" class=" name="ticket_global_stock" value="' . esc_attr( $identifier ) . '" ' . selected( $identifier === $current_option ) . '> ' . esc_html( $name ) . " </label>\n";
			}

			return $output;
		}

		/**
		 * Returns an array of standard stock mode options that can be
		 * reused by implementations.
		 *
		 * Format is: ['identifier' => 'Localized name', ... ]
		 *
		 * @return array
		 */
		protected function global_stock_mode_options() {
			return array(
				Tribe__Tickets__Global_Stock::GLOBAL_STOCK_MODE => __( 'Shared capacity with other tickets', 'event-tickets' ),
				Tribe__Tickets__Global_Stock::OWN_STOCK_MODE    => __( 'Set capacity for this ticket only', 'event-tickets' ),
			);
		}

		/**
		 * Tries to make data about global stock levels and global stock-enabled ticket objects
		 * available to frontend scripts.
		 *
		 * @param array $tickets
		 */
		public static function add_frontend_stock_data( array $tickets ) {
			// Add the frontend ticket form script as needed (we do this lazily since right now
			// it's only required for certain combinations of event/ticket
			if ( ! self::$frontend_script_enqueued ) {
				$url = Tribe__Tickets__Main::instance()->plugin_url . 'src/resources/js/frontend-ticket-form.js';
				$url = Tribe__Template_Factory::getMinFile( $url, true );
				wp_enqueue_script( 'tribe_tickets_frontend_tickets', $url, array( 'jquery' ), Tribe__Tickets__Main::VERSION, true );
			}

			self::$frontend_ticket_data = array_filter( array_merge( self::$frontend_ticket_data, $tickets ) );
			add_action( 'wp_footer', array( __CLASS__, 'enqueue_frontend_stock_data' ) );
		}

		/**
		 * Returns Ticket and RSVP Count for an Event
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return array
		 */
		public static function get_ticket_counts( $post_id ) {

			// if no post id return empty array
			if ( empty( $post_id ) ) {
				return [];
			}

			$tickets = self::get_all_event_tickets( $post_id );

			// if no tickets or rsvp return empty array
			if ( ! $tickets ) {
				return [];
			}

			/**
			 * This order is important so that tickets overwrite RSVP on
			 * the Buy Now Button on the front-end
			 */
			$types['rsvp']    = array(
				'count'     => 0,
				'stock'     => 0,
				'unlimited' => 0,
				'available' => 0,
			);
			$types['tickets'] = array(
				'count'     => 0, // count of ticket types currently for sale
				'stock'     => 0, // current stock of tickets available for sale
				'global'    => 0, // global stock ticket
				'unlimited' => 0, // unlimited stock tickets
				'available' => 0, // are tickets available for sale right now
			);

			foreach ( $tickets as $ticket ) {
				// If a ticket is not current for sale do not count it
				if ( ! tribe_events_ticket_is_on_sale( $ticket ) ) {
					continue;
				}

				// if ticket and not rsvp add to ticket array
				if ( 'Tribe__Tickets__RSVP' !== $ticket->provider_class ) {
					$types['tickets']['count'] ++;

					$global_stock_mode = $ticket->global_stock_mode();

					if ( $global_stock_mode === Tribe__Tickets__Global_Stock::GLOBAL_STOCK_MODE && 0 === $types['tickets']['global'] ) {
						$types['tickets']['global'] ++;
					} elseif ( $global_stock_mode === Tribe__Tickets__Global_Stock::GLOBAL_STOCK_MODE && 1 === $types['tickets']['global'] ) {
						continue;
					}

					if ( Tribe__Tickets__Global_Stock::GLOBAL_STOCK_MODE === $global_stock_mode ) {
						continue;
					}

					$stock_level = Tribe__Tickets__Global_Stock::CAPPED_STOCK_MODE === $global_stock_mode ? $ticket->global_stock_cap : $ticket->available();

					// whether the stock level is negative because it represents unlimited stock (`-1`)
					// or because it's oversold we normalize to `0` for the sake of displaying
					$stock_level = max( 0, (int) $stock_level );

					$types['tickets']['stock'] += $stock_level;

					if ( 0 !== $types['tickets']['stock'] ) {
						$types['tickets']['available'] ++;
					}

					if ( ! $ticket->manage_stock() || -1 === $ticket->capacity ) {
						$types['tickets']['unlimited'] ++;
						$types['tickets']['available'] ++;
					}
				} else {
					$types['rsvp']['count'] ++;

					$types['rsvp']['stock'] += $ticket->stock;
					if ( 0 !== $types['rsvp']['stock'] ) {
						$types['rsvp']['available'] ++;
					}

					if ( ! $ticket->manage_stock() ) {
						$types['rsvp']['unlimited'] ++;
						$types['rsvp']['available'] ++;
					}
				}
			}

			$global_stock = new Tribe__Tickets__Global_Stock( $post_id );
			$global_stock = $global_stock->is_enabled() ? $global_stock->get_stock_level() : 0;

			$types['tickets']['available'] += $global_stock;

			// If there's at least one ticket with shared capacity
			if ( ! self::tickets_own_stock( $post_id ) ) {
				$types['tickets']['stock'] += $global_stock;
			}

			return $types;
		}

		/**
		 * Returns if the all the tickets for an event
		 * have own stock
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return bool
		 */
		public static function tickets_own_stock( $post_id ) {
			$tickets = self::get_all_event_tickets( $post_id );

			// if no tickets or rsvp return false
			if ( ! $tickets ) {
				return false;
			}

			foreach ( $tickets as $ticket ) {

				// if ticket and not RSVP
				if ( 'Tribe__Tickets__RSVP' !== $ticket->provider_class ) {

					$global_stock_mode = $ticket->global_stock_mode();

					if ( Tribe__Tickets__Global_Stock::OWN_STOCK_MODE !== $global_stock_mode ) {
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Takes any global stock data and makes it available via a wp_localize_script() call.
		 */
		public static function enqueue_frontend_stock_data() {
			$data = array(
				'tickets' => [],
				'events'  => [],
			);

			foreach ( self::$frontend_ticket_data as $ticket ) {
				$post = $ticket->get_event();

				if ( empty( $post ) ) {
					continue;
				}

				$post_id      = $post->ID;
				$global_stock = new Tribe__Tickets__Global_Stock( $post_id );
				$stock_mode   = $ticket->global_stock_mode();

				$ticket_data = array(
					'event_id' => $post_id,
					'mode'     => $stock_mode,
					'cap'      => $ticket->capacity(),
				);

				if ( $ticket->managing_stock() ) {
					$ticket_data['stock'] = $ticket->available();
				}

				$data['events'][ $post_id ] = array(
					'stock' => $global_stock->get_stock_level(),
				);

				$data['tickets'][ $ticket->ID ] = $ticket_data;
			}

			wp_localize_script( 'tribe_tickets_frontend_tickets', 'tribe_tickets_stock_data', $data );
		}

		/**
		 * Returns the array of active modules/providers.
		 *
		 * @static
		 *
		 * @return array $active_modules {
		 *      Ticket modules
		 *
		 *      @param mixed $module A class which extends this one, acts as a ticket provider.
		 * }
		 */
		public static function modules() {
			/**
			 * Filters the available tickets modules
			 *
			 * @param array $active_modules {
			 *      Ticket modules
			 *
			 *      @param mixed $module A class which extends this one, acts as a ticket provider.
			 * }
			 */
			return apply_filters( 'tribe_tickets_get_modules', self::$active_modules );
		}

		/**
		 * Returns the class name of the default module/provider.
		 *
		 * @since 4.6
		 *
		 * @return string
		 */
		public static function get_default_module() {
			$modules = array_keys( self::modules() );

			if ( 1 === count( $modules ) ) {
				// There's only one, just return it.
				Tribe__Tickets__Tickets::$default_module = array_shift( $modules );
			} else {
				// Remove RSVP and PayPal tickets for this part
				unset(
					$modules[ array_search( 'Tribe__Tickets__RSVP', $modules ) ]
				);

				if ( ! empty( $modules ) ) {
					// We just return the first, so we don't show favoritism
					$sliced = array_slice( $modules, 0, 1 );
					self::$default_module = reset( $sliced );
				} else {
					// use PayPal tickets
					self::$default_module = 'Tribe__Tickets__Commerce__PayPal__Main';
				}
			}

			/**
			 * Filters the default commerce module (provider)
			 *
			 * @since 4.6
			 *
			 * @param string default ticket module class name
			 * @param array array of ticket module class names
			 */
			return apply_filters( 'tribe_tickets_get_default_module', self::$default_module, $modules );
		}

		/**
		 * Get all the tickets for an event. Queries all active modules/providers.
		 *
		 * @static
		 *
		 * @param int $post_id ID of parent "event" post
		 * @return array
		 */
		final public static function get_event_tickets( $post_id ) {
			$tickets = [];

			foreach ( self::modules() as $class => $module ) {
				/** @var Tribe__Tickets__Tickets $obj */
				$obj = call_user_func( array( $class, 'get_instance' ) );

				$provider_tickets = $obj->get_tickets( $post_id );

				if ( ! empty( $provider_tickets ) && is_array( $provider_tickets ) ) {
					$tickets[] = $provider_tickets;
				}
			}

			return ! empty( $tickets ) ? call_user_func_array( 'array_merge', $tickets ) : [];
		}

		/**
		 * Generates and returns the email template for a group of attendees.
		 *
		 * @param array $tickets
		 * @return string
		 */
		public function generate_tickets_email_content( $tickets ) {
			return tribe_tickets_get_template_part( 'tickets/email', null, array( 'tickets' => $tickets ), false );
		}

		/**
		 * Gets the view from the plugin's folder, or from the user's theme if found.
		 *
		 * @param string $template
		 * @return mixed|void
		 */
		public function getTemplateHierarchy( $template ) {

			if ( substr( $template, - 4 ) != '.php' ) {
				$template .= '.php';
			}

			if ( $theme_file = locate_template( array( 'tribe-events/' . $template ) ) ) {
				$file = $theme_file;
			} else {
				$file = $this->plugin_path . 'src/views/' . $template;
			}

			return apply_filters( 'tribe_events_tickets_template_' . $template, $file );
		}

		/**
		 * Queries ticketing providers to establish the range of tickets/pricepoints for the specified
		 * event and ensures those costs are included in the $costs array.
		 *
		 * @param  array $prices
		 * @param  int   $post_id
		 * @return array
		 */
		public function get_ticket_prices( array $prices, $post_id ) {
			// Iterate through all tickets from all providers
			foreach ( self::get_all_event_tickets( $post_id ) as $ticket ) {
				// No need to add the pricepoint if it is already in the array
				if ( in_array( $ticket->price, $prices ) ) {
					continue;
				}


				// An empty price property can be ignored (but do add if the price is explicitly set to zero)
				elseif ( isset( $ticket->price ) && is_numeric( $ticket->price ) ) {
					$prices[] = $ticket->price;
				}
			}

			return $prices;
		}

		/**
		 * Given a valid attendee ID, returns the event ID it relates to or else boolean false
		 * if it cannot be determined.
		 *
		 * @param  int   $attendee_id
		 * @return mixed int|bool
		 */
		public function get_event_id_from_attendee_id( $attendee_id ) {
			$provider_class     = new ReflectionClass( $this );
			$attendee_event_key = $this->get_attendee_event_key( $provider_class );

			if ( empty( $attendee_event_key ) ) {
				return false;
			}

			$post_id = get_post_meta( $attendee_id, $attendee_event_key, true );

			if ( empty( $post_id ) ) {
				return false;
			}

			return (int) $post_id;
		}

		/**
		 * Given a valid order ID, returns a single event ID it relates to or else boolean false
		 * if it cannot be determined.
		 *
		 * @see Use tribe_tickets_get_event_ids() to return an array of all event ids for an order
		 *
		 * @param  int   $order_id
		 * @return mixed int|bool
		 */
		public function get_event_id_from_order_id( $order_id ) {
			$provider_class     = new ReflectionClass( $this );
			$attendee_order_key = $this->get_attendee_order_key( $provider_class );
			$attendee_event_key = $this->get_attendee_event_key( $provider_class );
			$attendee_object    = $this->get_attendee_object( $provider_class );

			if ( empty( $attendee_order_key ) || empty( $attendee_event_key ) || empty( $attendee_object ) ) {
				return false;
			}

			$first_matched_attendee = get_posts( array(
				'post_type'  => $attendee_object,
				'meta_key'   => $attendee_order_key,
				'meta_value' => $order_id,
				'posts_per_page' => 1,
			) );

			if ( empty( $first_matched_attendee ) ) {
				return false;
			}

			return $this->get_event_id_from_attendee_id( $first_matched_attendee[0]->ID );
		}

		/**
		 * Returns the meta key used to link attendees with orders.
		 *
		 * This method provides backwards compatibility with older ticketing providers
		 * that do not define the expected class constants. Once a decent period has
		 * elapsed we can kill this method and access the class constants directly.
		 *
		 * @param  ReflectionClass $provider_class representing the concrete ticket provider
		 * @return string
		 */
		protected function get_attendee_order_key( $provider_class ) {
			$attendee_order_key = $provider_class->getConstant( 'ATTENDEE_ORDER_KEY' );

			if ( ! empty( $attendee_order_key ) ) {
				return (string) $attendee_order_key;
			}

			switch ( $this->class_name ) {
				case 'Tribe__Events__Tickets__Woo__Main':
					return '_tribe_wooticket_order';
				case 'Tribe__Events__Tickets__EDD__Main':
					return '_tribe_eddticket_order';
				case 'Tribe__Events__Tickets__Shopp__Main':
					return '_tribe_shoppticket_order';
				case 'Tribe__Events__Tickets__Wpec__Main':
					return '_tribe_wpecticket_order';
				default:
					return '';
			}
		}

		/**
		 * Returns the attendee object post type.
		 *
		 * This method provides backwards compatibility with older ticketing providers
		 * that do not define the expected class constants. Once a decent period has
		 * elapsed we can kill this method and access the class constants directly.
		 *
		 * @param  ReflectionClass $provider_class representing the concrete ticket provider
		 * @return string
		 */
		protected function get_attendee_object( $provider_class ) {
			$attendee_object = $provider_class->getConstant( 'ATTENDEE_OBJECT' );

			if ( ! empty( $attendee_object ) ) {
				return (string) $attendee_object;
			}

			switch ( $this->class_name ) {
				case 'Tribe__Events__Tickets__Woo__Main':
					return 'tribe_wooticket';
				case 'Tribe__Events__Tickets__EDD__Main':
					return 'tribe_eddticket';
				case 'Tribe__Events__Tickets__Shopp__Main':
					return 'tribe_shoppticket';
				case 'Tribe__Events__Tickets__Wpec__Main':
					return 'tribe_wpecticket';
				default:
					return '';
			}
		}

		/**
		 * Returns the meta key used to link attendees with the base event.
		 *
		 * This method provides backwards compatibility with older ticketing providers
		 * that do not define the expected class constants. Once a decent period has
		 * elapsed we can kill this method and access the class constants directly.
		 *
		 * If the meta key cannot be determined the returned string will be empty.
		 *
		 * @param  ReflectionClass $provider_class representing the concrete ticket provider
		 * @return string
		 */
		protected function get_attendee_event_key( $provider_class ) {
			$attendee_event_key = $provider_class->getConstant( 'ATTENDEE_EVENT_KEY' );

			if ( ! empty( $attendee_event_key ) ) {
				return (string) $attendee_event_key;
			}

			switch ( $this->class_name ) {
				case 'Tribe__Events__Tickets__Woo__Main':
					return '_tribe_wooticket_event';
				case 'Tribe__Events__Tickets__EDD__Main':
					return '_tribe_eddticket_event';
				case 'Tribe__Events__Tickets__Shopp__Main':
					return '_tribe_shoppticket_event';
				case 'Tribe__Events__Tickets__Wpec__Main':
					return '_tribe_wpecticket_event';
				default:
					return '';
			}
		}

		/**
		 * Process the attendee meta into an array with value, slug, and label
		 *
		 * @param int $product_id
		 * @param array $meta
		 * @return array
		 */
		public function process_attendee_meta( $product_id, $meta ) {
			$meta_values = [];

			if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) ) {
				return $meta_values;
			}

			$meta_field_objects = Tribe__Tickets_Plus__Main::instance()->meta()->get_meta_fields_by_ticket( $product_id );

			foreach ( $meta_field_objects as $field ) {
				$value = null;

				if ( 'checkbox' === $field->type ) {
					$field_prefix = $field->slug . '_';
					$value        = [];

					foreach ( $meta as $full_key => $check_value ) {
						if ( 0 === strpos( $full_key, $field_prefix ) ) {
							$short_key           = substr( $full_key, strlen( $field_prefix ) );
							$value[ $short_key ] = $check_value;
						}
					}

					if ( empty( $value ) ) {
						$value = null;
					}
				} elseif ( isset( $meta[ $field->slug ] ) ) {
					$value = $meta[ $field->slug ];
				}

				$meta_values[ $field->slug ] = array(
					'slug'  => $field->slug,
					'label' => $field->label,
					'value' => $value,
				);
			}

			return $meta_values;
		}

		/**
		 * Returns the meta key used to link ticket types with the base event.
		 *
		 * If the meta key cannot be determined the returned string will be empty.
		 * Subclasses can override this if they use a key other than 'event_key'
		 * for this purpose.
		 *
		 * @internal
		 *
		 * @return string
		 */
		public function get_event_key() {
			if ( property_exists( $this, 'event_key' ) ) {
				// EDD module uses a static event_key so we need to check for it or we'll fatal
				$prop = new ReflectionProperty( $this, 'event_key' );
				if ( $prop->isStatic() ) {
					return $prop->get_value();
				}

				return $this->event_key;
			}

			return '';
		}

		/**
		 * Returns an availability slug based on all tickets in the provided collection
		 *
		 * The availability slug is used for CSS class names and filter helper strings
		 *
		 * @since 4.2
		 *
		 * @param array $tickets Collection of tickets
		 * @param string $datetime Datetime string
		 * @return string
		 */
		public function get_availability_slug_by_collection( $tickets, $datetime = null ) {
			if ( ! $tickets ) {
				return;
			}

			if ( is_numeric( $datetime ) ) {
				$timestamp = $datetime;
			} elseif ( $datetime ) {
				$timestamp = strtotime( $datetime );
			} else {
				$timestamp = current_time( 'timestamp' );
			}

			$collection_availability_slug = 'available';
			$tickets_available = false;
			$slugs = [];

			foreach ( $tickets as $ticket ) {
				$availability_slug = $ticket->availability_slug( $timestamp );

				// if any ticket is available for this event, consider the availability slug as 'available'
				if ( 'available' === $availability_slug ) {
					// reset the collected slugs to "available" only
					$slugs = array( 'available' );
					break;
				}

				// track unique availability slugs
				if ( ! in_array( $availability_slug, $slugs ) ) {
					$slugs[] = $availability_slug;
				}
			}

			if ( 1 === count( $slugs ) ) {
				$collection_availability_slug = $slugs[0];
			} else {
				$collection_availability_slug = 'availability-mixed';
			}

			/**
			 * Filters the availability slug for a collection of tickets
			 *
			 * @param string Availability slug
			 * @param array Collection of tickets
			 * @param string Datetime string
			 */
			return apply_filters( 'event_tickets_availability_slug_by_collection', $collection_availability_slug, $tickets, $datetime );
		}

		/**
		 * Returns a tickets unavailable message based on the availability slug of a collection of tickets
		 *
		 * @since 4.2
		 *
		 * @param array $tickets Collection of tickets
		 * @return string
		 */
		public function get_tickets_unavailable_message( $tickets ) {
			$availability_slug = $this->get_availability_slug_by_collection( $tickets );
			$message           = null;
			$post_type = get_post_type();

			if ( 'tribe_events' == $post_type && function_exists( 'tribe_is_past_event' ) && tribe_is_past_event() ) {
				$events_label_singular_lowercase = tribe_get_event_label_singular_lowercase();
				$message = sprintf( esc_html__( 'Tickets are not available as this %s has passed.', 'event-tickets' ), $events_label_singular_lowercase );
			} elseif ( 'availability-future' === $availability_slug ) {
				/**
				 * Allows inclusion of ticket start sale date in unavailability message
				 *
				 * @since  4.7.6
				 *
				 * @param  bool	$display_date
				 */
				$display_date = apply_filters( 'tribe_tickets_unvailable_message_date', $display_date = true );

				/**
				 * Allows inclusion of ticket start sale time in unavailability message
				 *
				 * @since  4.7.6
				 *
				 * @param  bool	$display_time
				 */
				$display_time = apply_filters( 'tribe_tickets_unvailable_message_time', $display_time = false );

				// build message
				if ( $display_date ) {
					$start_sale_date = '';
					$start_sale_time = '';

					foreach ( $tickets as $ticket ) {
						// get the earliest start sale date
						if ( '' == $start_sale_date || $ticket->start_date < $start_sale_date ) {
							$start_sale_date = $ticket->start_date;
							$start_sale_time = $ticket->start_time;
						}
					}

					$date_format = tribe_get_date_format( true );
					$start_sale_date = Tribe__Date_Utils::reformat( $start_sale_date, $date_format );

					$message = __( 'Tickets will be available on ', 'event-tickets' );
					$message .= $start_sale_date;

					if ( $display_time ) {
						$time_format = tribe_get_time_format();
						$start_sale_time = Tribe__Date_Utils::reformat( $start_sale_time, $time_format );
						$message .= __( ' at ', 'event_tickets' ) . $start_sale_time;
					}
				} else {
					$message = __( 'Tickets are not yet available', 'event-tickets' );
				}
			} elseif ( 'availability-past' === $availability_slug ) {
				$message = __( 'Tickets are no longer available.', 'event-tickets' );
			} elseif ( 'availability-mixed' === $availability_slug ) {
				$message = __( 'There are no tickets available at this time.', 'event-tickets' );
			}

			/**
			 * Filters the unavailability message for a ticket collection
			 *
			 * @param string Unavailability message
			 * @param array Collection of tickets
			 */
			$message = apply_filters( 'event_tickets_unvailable_message', $message, $tickets );

			return $message;
		}

		/**
		 * Indicates that, from an individual ticket provider's perspective, the only tickets for the
		 * event are currently unavailable and unless a different ticket provider reports differently
		 * the "tickets unavailable" message should be displayed.
		 *
		 * @param array $tickets
		 * @param int $post_id ID of parent "event" post (defaults to the current post)
		 */
		public function maybe_show_tickets_unavailable_message( $tickets, $post_id = null ) {
			if ( null === $post_id ) {
				$post_id = get_the_ID();
			}

			$unavailable_tickets = self::$currently_unavailable_tickets;

			$existing_tickets = ! empty( $unavailable_tickets[ (int) $post_id ] )
				? $unavailable_tickets[ (int) $post_id ]
				: [];

			self::$currently_unavailable_tickets[ (int) $post_id ] = array_merge( $existing_tickets, $tickets );


		}

		/**
		 * Indicates that, from an individual ticket provider's perspective, the event does have some
		 * currently available tickets and so the "tickets unavailable" message should probably not
		 * be displayed.
		 *
		 * @param null $post_id
		 */
		public function do_not_show_tickets_unavailable_message( $post_id = null ) {
			if ( null === $post_id ) {
				$post_id = get_the_ID();
			}

			self::$posts_with_available_tickets[] = (int) $post_id;
		}

		/**
		 * If appropriate, display a "tickets unavailable" message.
		 */
		public function show_tickets_unavailable_message() {
			$post_id = (int) get_the_ID();

			// So long as at least one ticket provider has tickets available, do not show an unavailability message
			if ( in_array( $post_id, self::$posts_with_available_tickets ) ) {
				return;
			}

			// Bail if no ticket providers reported that all their tickets for the event were unavailable
			if ( empty( self::$currently_unavailable_tickets[ $post_id ] ) ) {
				return;
			}

			// Prepare the message
			$message = '<div class="tickets-unavailable">'
				. $this->get_tickets_unavailable_message( self::$currently_unavailable_tickets[ $post_id ] )
				. '</div>';

			/**
			 * Sets the tickets unavailable message.
			 *
			 * @param string $message
			 * @param int    $post_id
			 * @param array  $unavailable_event_tickets
			 */
			echo apply_filters( 'tribe_tickets_unavailable_message', $message, $post_id, self::$currently_unavailable_tickets[ $post_id ] );

			// Remove the record of unavailable tickets to avoid duplicate messages being rendered for the same event
			unset( self::$currently_unavailable_tickets[ $post_id ] );
		}

		/**
		 * Takes care of adding a "tickets unavailable" message by injecting it into the post content
		 * (where the template settings require such an approach).
		 *
		 * @param string $content
		 * @return string
		 */
		public function show_tickets_unavailable_message_in_content( $content ) {
			if ( ! $this->should_inject_ticket_form_into_post_content() ) {
				return $content;
			}

			ob_start();
			$this->show_tickets_unavailable_message();
			$form = ob_get_clean();

			$content .= $form;

			return $content;
		}
		// end Helpers

		/**
		 * Associates an attendee record with a user, typically the purchaser.
		 *
		 * The $user_id param is optional and when not provided it will default to the current
		 * user ID.
		 *
		 *
		 * @param int $attendee_id
		 * @param int $user_id
		 */
		protected function record_attendee_user_id( $attendee_id, $user_id = null ) {
			if ( null === $user_id ) {
				$user_id = get_current_user_id();
			}

			update_post_meta( $attendee_id, $this->attendee_user_id, (int) $user_id );
		}

		/**
		 * Prints the front-end tickets form in the post content.
		 *
		 * @param string $content The post original content.
		 *
		 * @return string The updated content.
		 */
		public function front_end_tickets_form_in_content( $content ) {
			if ( ! $this->should_inject_ticket_form_into_post_content() ) {
				return $content;
			}

			ob_start();
			$this->front_end_tickets_form( $content );
			$form    = ob_get_clean();
			$content .= $form;

			return $content;
		}

		/**
		 * Determines if this is a suitable opportunity to inject ticket form content into a post.
		 * Expects to run within "the_content".
		 *
		 * @return bool
		 */
		protected function should_inject_ticket_form_into_post_content() {
			global $post;

			// Prevents firing more then it needs too outside of the loop
			$in_the_loop = isset( $GLOBALS['wp_query']->in_the_loop ) && $GLOBALS['wp_query']->in_the_loop;

			if ( is_admin() || ! $in_the_loop ) {
				return false;
			}

			// if this isn't a post for some reason, bail
			if ( ! $post instanceof WP_Post ) {
				return false;
			}

			// if this isn't a supported post type, bail
			if ( ! tribe_tickets_post_type_enabled( $post->post_type ) ) {
				return false;
			}

			//  User is currently viewing/editing their existing tickets.
			if ( Tribe__Tickets__Tickets_View::instance()->is_edit_page() ) {
				return false;
			}

			// if this is a tribe_events post, let's bail because those post types are handled with a different hook
			if ( 'tribe_events' === $post->post_type ) {
				return false;
			}

			// if there aren't any tickets, bail
			$tickets = $this->get_tickets( $post->ID );
			if ( empty( $tickets ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Indicates if the user must be logged in in order to obtain tickets.
		 *
		 * @since 4.7
		 *
		 * @return bool
		 */
		public function login_required() {
			$requirements = (array) tribe_get_option( 'ticket-authentication-requirements', [] );

			return in_array( 'event-tickets_all', $requirements, true );
		}

		/**
		 * Provides a URL that can be used to direct users to the login form.
		 *
		 * @param int $post_id - the ID of the post to redirect to
		 *
		 * @return string
		 */
		public static function get_login_url( $post_id = null ) {
			if ( is_null( $post_id ) ) {
				$post_id   = get_the_ID();
			}

			$login_url = get_site_url( null, 'wp-login.php' );

			if ( $post_id ) {
				$login_url = add_query_arg( 'redirect_to', get_permalink( $post_id ), $login_url );
			}

			/**
			 * Provides an opportunity to modify the login URL used within frontend
			 * ticket forms (typically when they need to login before they can proceed).
			 *
			 * @param string $login_url
			 */
			return apply_filters( 'tribe_tickets_ticket_login_url', $login_url );
		}

		/**
		 * Adds or updates the capacity for a ticket.
		 *
		 * @since 4.7
		 *
		 * @param WP_Post|int $ticket
		 * @param array       $raw_data
		 * @param string      $save_type
		 */
		protected function update_capacity( $ticket, $data, $save_type ) {
			if ( empty( $data ) ) {
				return;
			}

			// set the default capacity to that of the event, if set, or to unlimited
			$default_capacity = Tribe__Utils__Array::get( $data, 'event_capacity', -1 );

			// Fetch capacity field, if we don't have it use default (defined above)
			$data['capacity'] = trim( Tribe__Utils__Array::get( $data, 'capacity', $default_capacity ) );

			// If empty we need to modify to the default
			if ( '' === $data['capacity'] ) {
				$data['capacity'] = $default_capacity;
			}

			// The only available value lower than zero is -1 which is unlimited
			if ( 0 > $data['capacity'] ) {
				$data['capacity'] = -1;
			}

			// Fetch the stock if defined, otherwise use Capacity field
			$data['stock'] = trim( Tribe__Utils__Array::get( $data, 'stock', $data['capacity'] ) );

			// If empty we need to modify to what every capacity was
			if ( '' === $data['stock'] ) {
				$data['stock'] = $data['capacity'];
			}

			// The only available value lower than zero is -1 which is unlimited
			if ( 0 > $data['stock'] ) {
				$data['stock'] = -1;
			}

			if ( -1 !== $data['capacity'] ) {
				if ( 'update' === $save_type ) {
					$totals        = tribe( 'tickets.handler' )->get_ticket_totals( $ticket->ID );
					$data['stock'] -= $totals['pending'] + $totals['sold'];
				}

				update_post_meta( $ticket->ID, '_manage_stock', 'yes' );
				update_post_meta( $ticket->ID, '_stock', $data['stock'] );
			} else {
				// unlimited stock
				delete_post_meta( $ticket->ID, '_stock_status' );
				update_post_meta( $ticket->ID, '_manage_stock', 'no' );
				delete_post_meta( $ticket->ID, '_stock' );
				delete_post_meta( $ticket->ID, Tribe__Tickets__Global_Stock::TICKET_STOCK_MODE );
				delete_post_meta( $ticket->ID, Tribe__Tickets__Global_Stock::TICKET_STOCK_CAP );
			}

			tribe_tickets_update_capacity( $ticket, $data['capacity'] );
		}

		/**
		 * @param bool $operation_did_complete
		 */
		protected function maybe_update_attendees_cache( $operation_did_complete ) {
			if ( $operation_did_complete && ! empty( $_POST['event_ID'] ) ) {
				$this->clear_attendees_cache( $_POST['event_ID'] );
			}
		}

		/**
		 * Clears the attendees cache for a given post
		 *
		 * @param int|WP_Post $post_id The parent post or ID
		 *
		 * @return bool Was the operation successful?
		 */
		public function clear_attendees_cache( $post_id ) {
			if ( $post_id instanceof WP_Post ) {
				$post_id = $post_id->ID;
			}

			/** @var Tribe__Post_Transient $post_transient */
			$post_transient = tribe( 'post-transient' );

			$cache_key = (int) $post_id;

			return $post_transient->delete( $cache_key, self::ATTENDEES_CACHE );
		}

		/**
		 * Returns the action tag that should be used to print the front-end ticket form.
		 *
		 * This value is set in the Events > Settings > Tickets tab and is distinct between RSVP
		 * tickets and commerce provided tickets.
		 *
		 * @return string
		 */
		public function get_ticket_form_hook() {
			if ( $this instanceof Tribe__Tickets__RSVP ) {
				$ticket_form_hook = Tribe__Settings_Manager::get_option( 'ticket-rsvp-form-location',
					'tribe_events_single_event_after_the_meta' );

				/**
				 * Filters the position of the RSVP tickets form.
				 *
				 * While this setting can be handled using the Events > Settings > Tickets > "Location of RSVP form"
				 * setting this filter allows developers to override the general setting in particular cases.
				 * Returning an empty value here will prevent the ticket form from printing on the page.
				 *
				 * @param string                  $ticket_form_hook The set action tag to print front-end RSVP tickets form.
				 * @param Tribe__Tickets__Tickets $this             The current instance of the class that's hooking its front-end ticket form.
				 */
				$ticket_form_hook = apply_filters( 'tribe_tickets_rsvp_tickets_form_hook', $ticket_form_hook, $this );
			} else {
				$ticket_form_hook = Tribe__Settings_Manager::get_option( 'ticket-commerce-form-location',
					'tribe_events_single_event_after_the_meta' );

				/**
				 * Filters the position of the commerce-provided tickets form.
				 *
				 * While this setting can be handled using the Events > Settings > Tickets > "Location of Tickets form"
				 * setting this filter allows developers to override the general setting in particular cases.
				 * Returning an empty value here will prevent the ticket form from printing on the page.
				 *
				 * @param string                  $ticket_form_hook The set action tag to print front-end commerce tickets form.
				 * @param Tribe__Tickets__Tickets $this             The current instance of the class that's hooking its front-end ticket form.
				 */
				$ticket_form_hook = apply_filters( 'tribe_tickets_commerce_tickets_form_hook', $ticket_form_hook, $this );
			}

			return $ticket_form_hook;
		}

		/**
		 * Creates a ticket object and calls the child save_ticket function
		 *
		 * @param int $post_id ID of parent "event" post
		 * @param array $data Raw post data
		 *
		 * @return boolean
		 */
		public function ticket_add( $post_id, $data ) {
			$ticket                   = new Tribe__Tickets__Ticket_Object();
			$ticket->ID               = isset( $data['ticket_id'] ) ? absint( $data['ticket_id'] ) : null;
			$ticket->name             = isset( $data['ticket_name'] ) ? esc_html( $data['ticket_name'] ) : null;
			$ticket->description      = isset( $data['ticket_description'] ) ? sanitize_textarea_field( $data['ticket_description'] ) : '';
			$ticket->price            = ! empty( $data['ticket_price'] ) ? filter_var( trim( $data['ticket_price'] ), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND ) : 0;
			$ticket->show_description = isset( $data['ticket_show_description'] ) ? 'yes' : 'no';
			$ticket->provider_class   = $this->class_name;
			$ticket->start_date       = null;
			$ticket->end_date         = null;
			$ticket->menu_order       = isset( $data['ticket_menu_order'] ) ? intval( $data['ticket_menu_order'] ) : null;

			tribe( 'tickets.handler' )->toggle_manual_update_flag( true );

			if ( ! empty( $ticket->price ) ) {
				// remove non-money characters
				$ticket->price = preg_replace( '/[^0-9\.\,]/Uis', '', $ticket->price );
			}

			if ( ! empty( $data['ticket_start_date'] ) ) {
				$start_datetime = Tribe__Date_Utils::maybe_format_from_datepicker( $data['ticket_start_date'] );

				if ( ! empty( $data['ticket_start_time'] ) ) {
					$start_datetime .= ' ' . $data['ticket_start_time'];
					$ticket->start_time = date( Tribe__Date_Utils::DBTIMEFORMAT, strtotime( ( $start_datetime ) ) );
				}

				$ticket->start_date = date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $start_datetime ) );
			}

			if ( ! empty( $data['ticket_end_date'] ) ) {
				$end_datetime = Tribe__Date_Utils::maybe_format_from_datepicker( $data['ticket_end_date'] );

				if ( ! empty( $data['ticket_end_time'] ) ) {
					$end_datetime .= ' ' . $data['ticket_end_time'];
					$ticket->end_time = date( Tribe__Date_Utils::DBTIMEFORMAT, strtotime( ( $end_datetime ) ) );
				}

				$ticket->end_date = date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $end_datetime ) );
			}

			/**
			 * Fired once a ticket has been created and added to a post
			 *
			 * @param int $post_id ID of parent "event" post
			 * @param Tribe__Tickets__Ticket_Object $ticket Ticket object
			 * @param array $data Submitted post data
			 */
			do_action( 'tribe_tickets_ticket_add', $post_id, $ticket, $data );

			// Pass the control to the child object
			$save_ticket = $this->save_ticket( $post_id, $ticket, $data );

			tribe( 'tickets.handler' )->toggle_manual_update_flag( false );

			$post = get_post( $post_id );
			if ( empty( $data['ticket_start_date'] ) ) {
				$date = strtotime( $post->post_date );
				$date = date( 'Y-m-d 00:00:00', $date );

				update_post_meta( $ticket->ID, tribe( 'tickets.handler' )->key_start_date, $date );
			}

			if ( empty( $data['ticket_end_date'] ) && 'tribe_events' === $post->post_type ) {
				$event_end = get_post_meta( $post_id, '_EventEndDate', true );
				update_post_meta( $ticket->ID, tribe( 'tickets.handler' )->key_end_date, $event_end );
			}

			tribe( 'tickets.version' )->update( $ticket->ID );

			return $save_ticket;
		}

		/**
		 * Get the saved or default ticket provider
		 *
		 * @since 4.7
		 *
		 * @param int $event_id - the post id of the event the ticket is attached to.
		 *
		 * @return string ticket module class name
		 */
		public static function get_event_ticket_provider( $event_id = null ) {

			// if  post ID is set, and a value has been saved, return the saved value
			if ( ! empty( $event_id ) ) {
				$saved = get_post_meta( $event_id, tribe( 'tickets.handler' )->key_provider_field, true );

				if ( ! empty( $saved ) ) {
					return $saved;
				}
			}

			// otherwise just return the default
			return self::get_default_module();
		}

		// @codingStandardsIgnoreEnd


		/**
		 * Get currency symbol
		 *
		 * @since 4.7.1
		 *
		 * @return string
		 */
		public function get_currency() {
			/**
			 * Default currency value for Tickets.
			 *
			 * @since 4.7.1
			 *
			 * @return string
			 */
			return (string) apply_filters( 'tribe_tickets_default_currency', 'USD' );
		}


		/**
		 * Returns all the tickets currently in the users cart.
		 *
		 * @since 4.9
		 *
		 * @param array $tickets
		 *
		 * @return array
		 */
		public function get_tickets_in_cart( $tickets ) {
			return $tickets;
		}

		/**
		 * Return whether we're currently on the checkout page for this Merchant.
		 *
		 * @since 4.9
		 *
		 * @return bool
		 */
		public function is_checkout_page() {
			return false;
		}

		/**
		 * If tickets exist in the cart for which we don't have meta info,
		 * redirect to the meta collection screen.
		 *
		 * @since 4.9
		 *
		 * @param string $redirect
		 */
		public function maybe_redirect_to_attendees_registration_screen( $redirect = null ) {

			// Bail if the meta storage class doesn't exist
			if ( ! class_exists( 'Tribe__Tickets_Plus__Meta__Storage' ) ) {
				return;
			}

			if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) ) {
				return;
			}

			// They're submitting RSVPs, do not include them for now
			if ( ! empty( $_POST['tribe_tickets_rsvp_submission'] ) ) {
				return;
			}

			if ( tribe( 'tickets.attendee_registration' )->is_on_page() ) {
				return;
			}

			// Return if not trying to access the checkout page
			if ( ! $this->is_checkout_page() ) {
				return;
			}

			/**
	 		 * Modify the tickets in cart, useful to
	 		 * change the contents for each vendor
			 * @since 4.9
			 *
			 * @param array
			*/
			$tickets_in_cart = apply_filters( 'tribe_tickets_tickets_in_cart', [] );

			// Bail if there are no tickets
			if ( empty( $tickets_in_cart ) ) {
				return;
			}

			$is_paypal = (bool) $redirect;

			/** @var Tribe__Tickets_Plus__Meta $meta */
			$meta = tribe( 'tickets-plus.main' )->meta();

			$cart_has_meta = true;

			// If the method exists (latest ET+ version), run it.
			if ( method_exists( $meta, 'cart_has_meta' ) ) {
				$cart_has_meta = $meta->cart_has_meta( $tickets_in_cart );
			}

			$cart_has_required_meta = $meta->cart_has_required_meta( $tickets_in_cart );
			$up_to_date             = tribe( 'tickets-plus.meta.contents' )->is_stored_meta_up_to_date( $tickets_in_cart );

			// If WooCommerce or EDD
			if ( ! $is_paypal ) {
				// Bail if there are no required fields in cart or the stored data is up to date
				// And they're submitting the Attendee Registration page
				if (
					isset( $_REQUEST['tribe_tickets_checkout'] )
						&& ( ! $cart_has_required_meta || $up_to_date )
				) {
					return;
				}

				// Bail If things are up to date and they haven't submitted the form
				// to access the registration page.
				if (
					$up_to_date
						&& ! isset( $_REQUEST['wootickets_process'] )
						&& ! isset( $_REQUEST['eddtickets_process'] )
				) {
					return;
				}

				// Bail if processing checkout for WooCommerce
				if ( isset( $_REQUEST['key'] ) ) {
					return;
				}
			}
			// If PayPal and cart does not have meta
			elseif ( ! $cart_has_meta ) {
				return;
			}

			$url = tribe( 'tickets.attendee_registration' )->get_url();

			$storage = new Tribe__Tickets_Plus__Meta__Storage();
			if ( ! empty( $redirect ) ) {
				$key = $storage->store_temporary_data( $redirect );
				/** @var \Tribe__Tickets__Commerce__PayPal__Main $commerce_paypal */
				$commerce_paypal = tribe( 'tickets.commerce.paypal' );

				$url = add_query_arg( array( 'event_tickets_redirect_to' => $key, 'provider' => $commerce_paypal->attendee_object ), $url );
			}

			wp_safe_redirect( $url, 307 );
			exit;
		}

		/************************
		 *                      *
		 *  Deprecated Methods  *
		 *                      *
		 ************************/
		// @codingStandardsIgnoreStart

		/**
		 * Tests if the user has the specified capability in relation to whatever post type
		 * the attendee object relates to.
		 *
		 * For example, if the attendee was generated for a ticket set up in relation to a
		 * post of the banana type, the generic capability "edit_posts" will be mapped to
		 * "edit_bananas" or whatever is appropriate.
		 *
		 * @internal for internal plugin use only (in spite of having public visibility)
		 *
		 * @deprecated  4.6.2
		 *
		 * @see    tribe( 'tickets.attendees' )->user_can
		 *
		 * @param  string $generic_cap
		 * @param  int    $attendee_id
		 *
		 * @return boolean
		 */
		public function user_can( $generic_cap, $attendee_id ) {
			_deprecated_function( __METHOD__, '4.6.2', 'tribe( "tickets.metabox" )->user_can( $generic_cap, $attendee_id )' );
			return tribe( 'tickets.metabox' )->user_can( $generic_cap, $attendee_id );
		}

		/**
		 * Check and set global capacity options for the "event" post
		 *
		 * @deprecated 4.6.2
		 * @since  4.6
		 *
		 * @return object ajax success object
		 */
		public function edit_global_capacity_level() {
			_deprecated_function( __METHOD__, '4.6.2', 'tribe_tickets_update_capacity' );
		}

		/**
		 * Sets an AJAX error, returns a JSON array and ends the execution.
		 *
		 * @deprecated 4.6.2
		 *
		 * @param string $message
		 */
		final protected function ajax_error( $message = '' ) {
			_deprecated_function( __METHOD__, '4.6.2', 'wp_send_json_error()' );
			wp_send_json_error( $message );
		}

		/**
		 * Sets an AJAX response, returns a JSON array and ends the execution.
		 *
		 * @deprecated 4.6.2
		 *
		 * @param mixed $data
		 */
		final protected function ajax_ok( $data ) {
			_deprecated_function( __METHOD__, '4.6.2', 'wp_send_json_success()' );
			wp_send_json_success( $data );
		}
	}
}
