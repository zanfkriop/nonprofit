<?php

namespace GeminiLabs\SiteReviews\Controllers;

use GeminiLabs\SiteReviews\Abstracts\Controller;
use GeminiLabs\SiteReviews\Database\OptionManager;
use GeminiLabs\SiteReviews\Modules\Notice;
use GeminiLabs\SiteReviews\Modules\Settings;

class SettingsController extends Controller
{
	/**
	 * @param array $input
	 * @return array
	 * @callback register_setting
	 */
	public function callbackRegisterSettings( $input )
	{
		if( !is_array( $input )) {
			$input = ['settings' => []];
		}
		$key = key( $input );
		$message = '';
		if( $key == 'settings' ) {
			$message = __( 'Settings updated.', 'site-reviews' );
			glsr( Notice::class )->addSuccess( $message );
		}
		$options = array_replace_recursive( glsr( OptionManager::class )->all(), $input );
		$options = $this->sanitizeSubmissions( $input, $options );
		$options = $this->sanitizeTranslations( $input, $options );
		return $options;
	}

	/**
	 * @return void
	 * @action admin_init
	 */
	public function registerSettings()
	{
		$settings = apply_filters( 'site-reviews/settings', ['settings'] );
		foreach( $settings as $setting ) {
			register_setting(
				Application::ID.'-'.$setting,
				OptionManager::databaseKey(),
				[$this, 'callbackRegisterSettings']
			);
		}
		glsr( Settings::class )->register();
	}

	/**
	 * @return array
	 */
	protected function sanitizeSubmissions( array $input, array $options )
	{
		if( isset( $input['settings']['submissions'] )) {
			$inputForm = $input['settings']['submissions'];
			$options['settings']['submissions']['required'] = isset( $inputForm['required'] )
				? $inputForm['required']
				: [];
		}
		return $options;
	}

	/**
	 * @return array
	 */
	protected function sanitizeTranslations( array $input, array $options )
	{
		if( isset( $input['settings']['translations'] )) {
			$options['settings']['translations'] = array_values( array_filter( $input['settings']['translations'] ));
			array_walk( $options['settings']['translations'], function( &$string ) {
				if( isset( $string['s2'] )) {
					$string['s2'] = wp_strip_all_tags( $string['s2'] );
				}
				if( isset( $string['p2'] )) {
					$string['p2'] = wp_strip_all_tags( $string['p2'] );
				}
			});
		}
		return $options;
	}
}
