<?php

/**
 * Flickr service for Media Explorer.
 *
 * @since 0.1.0
 * @author Akeda Bagus <admin@gedex.web.id>
 */
class MEXP_Flickr_Service extends MEXP_Service {

	/**
	 * Service name.
	 */
	const NAME = 'flickr_mexp_service';

	/**
	 * Number of images to return by default.
	 */
	const DEFAULT_PER_PAGE = 19;

	/**
	 * Constructor.
	 *
	 * Sets template.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function __construct() {
		$tpl = new MEXP_Flickr_Template();
		$this->set_template( $tpl );
	}

	/**
	 * Fired when the service is loaded.
	 *
	 * Enqueue static assets.
	 *
	 * Hooks into MEXP tabs and labels.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function load() {
		add_action( 'mexp_enqueue', array( $this, 'enqueue_statics' ) );
		add_action( 'mexp_tabs',    array( $this, 'tabs' ), 10, 1 );
		add_action( 'mexp_labels',  array( $this, 'labels' ), 10, 1 );
	}

	/**
	 * Enqueue static assets (CSS/JS).
	 *
	 * @since 0.1.0
	 * @action mexp_enqueue
	 * @return void
	 */
	public function enqueue_statics() {
		wp_enqueue_style(
			'mexp-flickr',
			trailingslashit( MEXP_FLICKR_URL ) . 'css/mexp-flickr.css',
			array( 'mexp' ),
			MEXP_Flickr::VERSION
		);
	}

	/**
	 * Returns an array of tabs (routers) for the service's media manager panel.
	 *
	 * @since 0.1.0
	 * @filter mexp_tabs.
	 * @param array $tabs Associative array of default tab items.
	 * @return array Associative array of tabs. The key is the tab ID and the value is an array of tab attributes.
	 */
	public function tabs( array $tabs ) {
		$tabs[ self::NAME ] = array(
			'all' => array(
				'text'       => _x( 'All', 'Tab title', 'mexp-flickr' ),
				'defaultTab' => true,
			),
			'tags' => array(
				'text' => _x( 'By Tags', 'Tab title', 'mexp-flickr' ),
			),
			'user_id' => array(
				'text' => _x( 'By User', 'Tab title', 'mexp-flickr' ),
			),
		);

		return $tabs;
	}

	/**
	 * Returns an array of custom text labels for this service.
	 *
	 * @since 0.1.0
	 * @filter mexp_labels
	 * @param array $labels Associative array of default labels.
	 * @return array Associative array of labels.
	 */
	public function labels( array $labels ) {
		$labels[ self::NAME ] = array(
			'title'     => __( 'Insert Flickr Photos', 'mexp-flickr' ),
			'insert'    => __( 'Insert', 'mexp-flickr' ),
			'noresults' => __( 'No photos matched your search query', 'mexp-flickr' ),
			'loadmore'  => __( 'Load more photos', 'mexp-flickr' ),
		);

		return $labels;
	}

	public function request( array $request ) {
		if ( is_wp_error( $flickr = $this->_get_client() ) )
			return $flickr;


		if ( ! isset( $request['page'] ) )
			$page = 1;
		else
			$page = $request['page'];

		$per_page = (int) apply_filters( 'mexp_flickr_per_page', self::DEFAULT_PER_PAGE );

		$method         = 'flickr.photos.search';
		$format         = 'json';
		$nojsoncallback = 1;
		$request_args   = compact( 'method', 'format', 'nojsoncallback', 'page', 'per_page' );

		$params = $request['params'];
		switch ( $params['tab'] ) {
			case 'tags':
				$request_args['tags'] = sanitize_text_field( $params['tags'] );
				break;
			case 'user_id':
				$request_args['user_id'] = sanitize_text_field( $params['user_id'] );
				break;
			case 'all':
			default:
				$request_args['text'] = sanitize_text_field( $params['text'] );
				break;
			/**
			 * @todo Implement lat, lon, and radius arguments.
			 * @see  http://www.flickr.com/services/api/flickr.photos.search.html
			 *
			 */
		}

		// Response from Flickr.
		$search_response = $flickr->request( $request_args );
		if ( is_wp_error( $search_response ) )
			return $search_response;

		// Creates the response for the API.
		$response = new MEXP_Response();

		if ( ! isset( $search_response['photos'] ) )
			return false;

		if ( ! isset( $search_response['photos']['photo'] ) )
			return false;

		$photo_url_template = 'http://www.flickr.com/photos/%s/%s';
		$thumbnail_template = 'http://farm%s.staticflickr.com/%s/%s_%s_m.jpg';

		foreach ( $search_response['photos']['photo'] as $photo ) {
			$item = new MEXP_Response_Item();

			$item->set_id( $photo['id'] );
			$item->set_url( sprintf( $photo_url_template, $photo['owner'], $photo['id'] ) );
			$item->set_content( $photo['title'] );
			$item->set_thumbnail( sprintf( $thumbnail_template, $photo['farm'], $photo['server'], $photo['id'], $photo['secret'] ) );

			$response->add_item( $item );
		}

		$response->add_meta( 'page', $page + 1 );

		return $response;
	}

	private function _get_client() {
		$api_key = (string) apply_filters( 'mexp_flickr_api_key', '' );

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'mexp_flickr_missing_api_key',
				__( 'Missing API key for Flickr', 'mexp-flickr' )
			);
		}

		return new MEXP_Flickr_API_Client( $api_key );
	}
}
