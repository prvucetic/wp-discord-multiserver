<?php
/**
 * WP Discord Post Multiserver
 *
 * @author      Predrag Vucetic
 * @license     GPL-2.0+
 *
 * Plugin Name: WP Discord Post Multiserver
 * Plugin URI:  https://wordpress.org/plugins/wp-discord-post-multiserver/
 * Description: A WP Discord Post extension that allows multiserver integration.
 * Version:     1.0.0
 * Author:      Predrag Vucetic
 * Author URI:  https://predragvucetic.com/
 * Text Domain: wp-discord-post-multiserver
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Discord_Post_Multiserver {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'settings_init' ), 100 );
		add_action( 'wp_discord_post_before_request', array( $this, 'wp_discord_post_before_request_action' ), 10, 2 );
		$this->load_textdomain();
	}
	
	public function wp_discord_post_before_request_action($request, $webhook_url) {
		
		$webhook_url_multi = get_option( 'wp_discord_post_webhook_url_multi' );
		if ( !empty( $webhook_url_multi ) ) {
		
			$sites = explode(PHP_EOL, $webhook_url_multi);
		
			foreach ($sites as $site) {
    			$elements = explode('|', $site, 2);
    			if(count($elements) == 2){
    				$body = json_decode( $request['body'], true );
					$body['username'] = esc_html( $elements[0] );
					$request['body'] = wp_json_encode( $body );
    				$response = wp_remote_post( esc_url( $elements[1] ), $request );
    			}
			}
		
		}
	}

	public function settings_init() {
		add_settings_section(
			'wp_discord_post_multiserver_settings',
			esc_html__( 'Multiserver', 'wp-discord-post' ),
			array( $this, 'settings_callback' ),
			'wp-discord-post'
		);

		add_settings_field(
			'wp_discord_post_webhook_url_multi',
			esc_html__( 'Additional Servers', 'wp-discord-post' ),
			array( $this, 'print_webhook_url_field' ),
			'wp-discord-post',
			'wp_discord_post_multiserver_settings'
		);

		register_setting( 'wp-discord-post', 'wp_discord_post_webhook_url_multi' );
	}

	public function settings_callback() {
		esc_html_e( 'Configure WP Discord Post multiserver integration', 'wp-discord-post' );
	}

	public function print_webhook_url_field() {
		$value = get_option( 'wp_discord_post_webhook_url_multi' );

		echo '<textarea style="width:500px;height:150px;white-space: nowrap;" name="wp_discord_post_webhook_url_multi">' . esc_textarea( $value ) . '</textarea><br/>';
		echo '<span class="description">' . sprintf( esc_html__( 'Put each additional server on a new line. Line should contain title followed by webhook URL, separated by vertical bar (|) (e.g. `Custom Title|https://discordapp.com...`)', 'wp-discord-post' ) ) . '</span>';
	}

	public function load_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-discord-post' );
		load_textdomain( 'wp-discord-post', WP_LANG_DIR . '/wp-discord-post/discord-post-' . $locale . '.mo' );
		load_plugin_textdomain( 'wp-discord-post', false, plugin_basename( __DIR__ ) . '/languages' );
	}
}

new WP_Discord_Post_Multiserver();
