<?php

class MEXP_Flickr_API_Client {

	const BASE_URL = 'http://api.flickr.com/services/rest';

	/**
	 * API Key.
	 *
	 * @var string
	 * @access private
	 */
	private $api_key;

	/**
	 * Constructor. Sets API key.
	 *
	 * @param string $api API key
	 * @return void
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Makes a request to Flickr API.
	 *
	 * @param array $args
	 * @return WP_Error|array
	 */
	public function request( array $args ) {
		if ( ! isset( $args['api_key'] ) )
			$args['api_key'] = $this->api_key;

		foreach ( $args as $key => $value ) {
			$args[ $key ] = urlencode( $value );
		}

		$url      = add_query_arg( $args, self::BASE_URL );
		$response = (array) wp_remote_get( $url );

		if ( ! isset( $response['response']['code'] ) || 200 !== (int) $response['response']['code'] ) {
			return new WP_Error(
				'mexp_flickr_unexpected_response',
				sprintf( __( 'Unexpected response from Flickr API with status code %s', 'mexp-flickr' ), $response['response']['code'] )
			);
		}

		$decoded_response = json_decode( $response['body'], true );
		if ( isset( $decoded_response['stat'] ) && 'fail' === $decoded_response['stat'] ) {
			return new WP_Error(
				'mexp_flickr_fail_stat_response',
				$decoded_response['message']
			);
		}

		return $decoded_response;
	}
}
