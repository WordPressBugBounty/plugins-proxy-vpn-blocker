<?php
/**
 * Proxy & VPN Blocker Plugin Settings API
 *
 * @package  Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Proxy & VPN Blocker Plugin Settings API Class.
 */
class Proxy_VPN_Blocker_Admin_API {
	/**
	 * Generates HTML for displaying fields.
	 *
	 * @param name $data field data.
	 * @param name $post false.
	 * @param name $echo true.
	 */
	public function display_field( $data = array(), $post = false, $echo = true ) {

		// Get field info.
		if ( isset( $data['field'] ) ) {
			$field = $data['field'];
		} else {
			$field = $data;
		}

		// Check for prefix on option name.
		$option_name = '';
		if ( isset( $data['prefix'] ) ) {
			$option_name = $data['prefix'];
		}

		// Get saved data.
		$data = '';
		if ( $post ) {

			// Get saved field data.
			$option_name .= $field['id'];
			$option       = get_post_meta( $post->ID, $field['id'], true );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		} else {

			// Get saved option.
			$option_name .= $field['id'];
			$option       = get_option( $option_name );

			// Get data to display in field.
			if ( isset( $option ) ) {
				$data = $option;
			}
		}

		// Show default data if no option saved and default is supplied.
		if ( false === $data && isset( $field['default'] ) ) {
			$data = $field['default'];
		} elseif ( false === $data ) {
			$data = '';
		}

		$html = '';

		switch ( $field['type'] ) {

			case 'text':
				$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" type="text" autocomplete="off" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
				break;

			case 'apikey':
				$current_key = get_option( 'pvb_proxycheckio_API_Key_field', '' );
				$has_key     = ! empty( $current_key );

				if ( $has_key ) {
					$html .= '<div class="api-key-status-card" style="border: 1px solid #00a32a; border-radius: 11px; padding: 15px; background: #f0f8f0; margin-bottom: 15px; max-width: 100%;">';
					$html .= '<div style="display: flex; align-items: center; justify-content: space-between;">';
					$html .= '<div><span style="color: #00a32a; font-weight: 600;">✅ API Key Active</span><br><small style="color: #666;">Key is configured and encrypted</small></div>';
					$html .= '<button type="button" class="pvbsecondary small" onclick="toggleapiKeyUpdate(\'' . esc_attr( $field['id'] ) . '\')">Change API Key</button>';
					$html .= '</div>';
					$html .= '</div>';

					$html .= '<div id="' . esc_attr( $field['id'] ) . '-update" style="display: none;">';
					$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" type="text" autocomplete="off" name="' . esc_attr( $option_name ) . '" placeholder="Enter new API key" value="" style="width: 100%; max-width: 400px;" />';
					$html .= '<p class="description">Enter a new API key to replace the current one, or leave blank to keep existing key.</p>';
					$html .= '</div>';

					$html .= '<script>
					        function toggleapiKeyUpdate(fieldId) {
								const updateDiv = document.getElementById(fieldId + "-update");
								const toggleBtn = document.getElementById(fieldId + "-toggle");
								const inputField = document.getElementById(fieldId);
								
								if (updateDiv.style.display === "none" || updateDiv.style.display === "") {
									// Show the update field
									updateDiv.style.display = "block";
									toggleBtn.textContent = "Hide";
									inputField.focus();
								} else {
									// Hide the update field and clear input
									updateDiv.style.display = "none";
									toggleBtn.textContent = "Update Key";
									inputField.value = "";
								}
							}
					</script>';
				} else {
					$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" type="text" autocomplete="off" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" />';
					$html .= '<p class="description">Enter your proxycheck.io API key to enable enhanced functionality.</p>';
				}
				break;

			case 'cors_public':
				$html .= '<input class="pvb" id="' . esc_attr( $field['id'] ) . '" autocomplete="off" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" />' . "\n";
				break;

			case 'textarea':
				$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" autocomplete="off" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>' . "\n";
				break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked';
				}
				$html .= '<div class="onoffswitch-container">' . "\n";
				$html .= '	<div class="onoffswitch">' . "\n";
				$html .= '		<input autocomplete="off" tabindex="0" class="onoffswitch-checkbox" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" ' . $checked . '>' . "\n";
				$html .= '		<label class="onoffswitch-label" for="' . esc_attr( $field['id'] ) . '">' . "\n";
				$html .= '			<span class="onoffswitch-inner"></span>' . "\n";
				$html .= '			<span class="onoffswitch-switch"></span>' . "\n";
				$html .= '		</label>';
				$html .= '	</div>' . "\n";
				$html .= '</div>' . "\n";
				break;

			case 'textslider':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="60" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue" id="sliderrangeoutput"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-riskscore-proxy':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange2" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="99" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue2" id="sliderrangeoutput2"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-riskscore-vpn':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange3" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="1" max="99" step="1" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue3" id="sliderrangeoutput3"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'textslider-good-ip-cache-time':
				$html .= '<div class="range-slider">' . "\n";
				$html .= '<input class="range_sliderrange" id="sliderrange4" name="' . esc_attr( $option_name ) . '" type="range" value="' . esc_attr( $data ) . '" min="10" max="240" step="10" autocomplete="off">' . "\n";
				$html .= '<span class="range_slidervalue4" id="sliderrangeoutput4"></span>' . "\n";
				$html .= '</div>';
				break;

			case 'checkbox_multi':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( in_array( $k, (array) $data, true ) ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'radio':
				foreach ( $field['options'] as $k => $v ) {
					$checked = false;
					if ( $k === $data ) {
						$checked = true;
					}
					$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
				}
				break;

			case 'select':
				$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( $k === $data ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_country_multi':
				$html .= '<select class="js-select2pvb-list form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_other':
				$html .= '<select class="js-select2pvb-placeholder-default form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" autocomplete="off">';
				$html .= '<option></option>';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_other_multi':
				$http_referrer_addresses = '';
				if ( ! empty( get_option( 'pvb_http_referrer_addresses' ) ) ) {
					$http_referrer_addresses = get_option( 'pvb_http_referrer_addresses' );
				}
				$html .= '<select class="js-select2pvb-tags form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple" autocomplete="off">';
				if ( is_array( $http_referrer_addresses ) ) {
					foreach ( $http_referrer_addresses as $k => $v ) {
						$html .= '<option selected="true" value="' . esc_attr( $v ) . '">' . $v . '</option>';
					}
				}
				$html .= '</select> ';
				break;

			case 'select_ip_header_type':
				// Show CloudFlare Warning if CloudFlare Header is Detected but Not Selected.
				$ip_header_type = '';
				if ( ! empty( get_option( 'pvb_option_ip_header_type' ) ) ) {
					$ip_header_type = get_option( 'pvb_option_ip_header_type' );
				}
				if ( is_array( $ip_header_type ) ) {
					if ( isset( $field['field-warn-h'] ) && 'HTTP_CF_CONNECTING_IP' !== $ip_header_type[0] && isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
						$html .= '<div class="field-warning"><i class="pvb-fa-icon-exclamation-triangle"></i> ' . $field['field-warn-h'] . '</div>' . "\n";
					}
				}
				$html .= '<select class="js-select2pvb-header-custom form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_paths_multi':
				$html .= '<select class="js-select2pvb-header-custom form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple" autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_page_single':
				$html .= '<select class="js-select2pvb-list form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '"  autocomplete="off">';
				$html .= '<option></option>';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'select_pages_multi':
				$html .= '<select class="js-select2pvb-list form-control" style="width: 100%" data-placeholder="' . esc_attr( $field['placeholder'] ) . '" name="' . esc_attr( $option_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple autocomplete="off">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
				break;

			case 'clearsettings':
				$checked = '';
				if ( $data && 'on' === $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<label class="switch">' . "\n";
				$html .= '<input autocomplete="off" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" id="' . esc_attr( $field['id'] ) . '" ' . $checked . '>' . "\n";
				$html .= '<div class="sliderdel round">' . "\n";
				$html .= '<span class="sliderdelon">YES</span>' . "\n";
				$html .= '<span class="sliderdeloff">NO</span>' . "\n";
				$html .= '</div>' . "\n";
				$html .= '</label>';
				break;

			case 'editor':
				wp_editor(
					$data,
					$option_name,
					array(
						'textarea_name' => $option_name,
					)
				);
				break;

			case 'hidden_key_field':
				$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="hidden" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( uniqid() ) . '" />' . "\n";
				break;

			case 'deprecated_setting':
				$html .= '' . "\n";
				break;
		}

		switch ( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
			case 'country_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
				if ( isset( $field['field-note'] ) ) {
					$html .= '<div class="field-note"><i class="pvb-fa-icon-exclamation-circle"></i> ' . $field['field-note'] . '</div>' . "\n";
				}
				if ( isset( $field['field-warning'] ) ) {
					$html .= '<div class="field-warning"><i class="pvb-fa-icon-exclamation-triangle"></i> ' . $field['field-warning'] . '</div>' . "\n";
				}
				break;

			default:
				$html .= '<p class="description">' . $field['description'] . '</p>' . "\n";
				if ( isset( $field['field-note'] ) ) {
					$html .= '<div class="field-note"><i class="pvb-fa-icon-exclamation-circle"></i> ' . $field['field-note'] . '</div>' . "\n";
				}
				if ( isset( $field['field-warning'] ) ) {
					$html .= '<div class="field-warning"><i class="pvb-fa-icon-exclamation-triangle"></i> ' . $field['field-warning'] . '</div>' . "\n";
				}
				break;
		}

		if ( ! $echo ) {
			return $html;
		}

		//phpcs:ignore
		echo $html;
	}

	/**
	 * Validate form field
	 *
	 * @param  string $data Submitted value.
	 * @param  string $type Type of field to validate.
	 * @return string       Validated value.
	 */
	public function validate_field( $data = '', $type = 'text' ) {

		switch ( $type ) {
			case 'text':
				$data = esc_attr( $data );
				break;
		}

		return $data;
	}

	/**
	 * Validate checkbox
	 *
	 * @param  string $data Submitted value.
	 * @param  string $type Type of field to validate.
	 * @return string       Validated value.
	 */
	public function validate_field_checkbox( $data = '', $type = 'checkbox' ) {

		switch ( $type ) {
			case 'checkbox':
				$data = esc_attr( $data );
				break;
		}

		return $data;
	}
}
