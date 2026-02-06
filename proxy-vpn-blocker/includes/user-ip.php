<?php
/**
 * Proxy & VPN Blocker User Registration and last login IP logging.
 *
 * @package Proxy & VPN Blocker
 */

/**
 * Get a users IP and other information when they register. Save this as user meta.
 *
 * @since 1.8.3
 * @param type $user_id from user_register hook.
 */
function pvb_user_register_ip_save( $user_id ) {
	$visitor_ip_address = pvb_get_visitor_ip_address();
	require_once plugin_dir_path( __DIR__ ) . 'proxycheckio-api-call.php';
	if ( isset( $visitor_ip_address ) ) {
		add_user_meta( $user_id, 'registration_ip', $visitor_ip_address );

		// Make a request to the remote API to get IP & risk data.
		$proxycheck_answer = proxycheck_function( $visitor_ip_address, 1, 0, 1 );

		$registration_ip_metrics = array(
			'risk'          => (int) $proxycheck_answer[3],
			'country_code'  => $proxycheck_answer[5],
			'country'       => $proxycheck_answer[1],
			'city'          => $proxycheck_answer[6],
			'datetime_blog' => current_datetime()->format( 'Y-m-d H:i:s' ),
		);

		update_user_meta( $user_id, 'registration_ip_metrics', $registration_ip_metrics );
	}
}
add_action( 'user_register', 'pvb_user_register_ip_save', 10, 1 );

/**
 * Get a users IP and other information when they first log in. Save this as user meta.
 * Updates this information with each log in.
 *
 * @since 1.8.3
 * @param type $user_login from wp_login hook.
 * @param type $user from wp_login hook.
 */
function pvb_user_login( $user_login, $user ) {
	$visitor_ip_address = pvb_get_visitor_ip_address();
	require_once plugin_dir_path( __DIR__ ) . 'proxycheckio-api-call.php';
	if ( isset( $visitor_ip_address ) ) {
		$last_login_ip = '';
		if ( '' !== get_user_meta( $user->ID, 'last_login_ip', true ) ) {
			$last_login_ip = get_user_meta( $user->ID, 'last_login_ip', true );
		}
		if ( $last_login_ip !== $visitor_ip_address ) {
			update_user_meta( $user->ID, 'last_login_ip', $visitor_ip_address );
		}
		// Make a request to the remote API to get IP & risk data.
		$proxycheck_answer = proxycheck_function( $visitor_ip_address, 1, 0, 1 );

		$last_login_ip_metrics = array(
			'risk'          => (int) $proxycheck_answer[3],
			'country_code'  => $proxycheck_answer[5],
			'country'       => $proxycheck_answer[1],
			'city'          => $proxycheck_answer[6],
			'datetime_blog' => current_datetime()->format( 'Y-m-d H:i:s' ),
		);

		update_user_meta( $user->ID, 'last_login_ip_metrics', $last_login_ip_metrics );
	}
}
add_action( 'wp_login', 'pvb_user_login', 10, 2 );

/**
 * Add column to user table for displaying IP information
 *
 * @since 1.8.3
 * @param type $column from manage_users_columns filter.
 */
function new_modify_user_table( $column ) {
	$column['user_ip'] = 'User IP Address';
	return $column;
}
add_filter( 'manage_users_columns', 'new_modify_user_table' );

/**
 * Display and format user IP information within the column created previously.
 *
 * @since 1.8.3
 * @param type $user_ip_html from manage_users_custom_column hook.
 * @param type $column_name from manage_users_columns set above.
 * @param type $user_id from manage_users_custom_column hook.
 */
function new_modify_user_table_row( $user_ip_html, $column_name, $user_id ) {
	$last_login_ip_metrics   = get_user_meta( $user_id, 'last_login_ip_metrics', false );
	$last_login_country_full = 'Unknown Country';
	$last_login_city         = 'Unknown City';
	$last_login_country      = 'Unknown Country';
	$login_flag              = plugins_url( '../assets/img/country_flags/NO-FLAG.png', __FILE__ );
	if ( isset( $last_login_ip_metrics[0]['country_code'] ) ) {
		$last_login_country = $last_login_ip_metrics[0]['country_code'];
		$login_flag         = plugins_url( '../assets/img/country_flags/' . $last_login_country . '.png', __FILE__ );
		if ( isset( $last_login_ip_metrics[0]['country'] ) ) {
			$last_login_country_full = $last_login_ip_metrics[0]['country'];
		}
		if ( isset( $last_login_ip_metrics[0]['city'] ) ) {
			$last_login_city = $last_login_ip_metrics[0]['city'];
		}
	}

	if ( isset( $last_login_ip_metrics[0]['risk'] ) ) {
		$last_login_risk = $last_login_ip_metrics[0]['risk'];
	} else {
		$last_login_risk = 'N/A';
	}

	if ( $last_login_risk <= 33 ) {
		$riskl_color = '#91a02b';
		$riskl_bg    = '#f4f7e1';
	} elseif ( $last_login_risk >= 34 && $last_login_risk <= 65 ) {
		$riskl_color = '#f5a91b';
		$riskl_bg    = '#fef3de';
	} elseif ( $last_login_risk >= 66 ) {
		$riskl_color = '#b50000';
		$riskl_bg    = '#ffe8e8';
	}
	if ( 'N/A' === $last_login_risk ) {
		$riskl_color = '#000000';
		$riskl_bg    = '#e9e9e9';
	}

	if ( ! empty( $last_login_ip_metrics[0]['datetime_blog'] ) ) {
		$login_time = $last_login_ip_metrics[0]['datetime_blog'];
	} else {
		$login_time = 'Uknown';
	}

	$registration_ip_metrics   = get_user_meta( $user_id, 'registration_ip_metrics', false );
	$registration_country_full = 'Unknown Country';
	$registration_city         = 'Unknown City';
	$registration_flag         = plugins_url( '../assets/img/country_flags/NO-FLAG.png', __FILE__ );
	if ( isset( $registration_ip_metrics[0]['country_code'] ) ) {
		$registration_country = $registration_ip_metrics[0]['country_code'];
		$registration_flag    = plugins_url( '../assets/img/country_flags/' . $registration_country . '.png', __FILE__ );
		if ( isset( $registration_ip_metrics[0]['country'] ) ) {
			$registration_country_full = $registration_ip_metrics[0]['country'];
		}
		if ( isset( $registration_ip_metrics[0]['city'] ) ) {
			$registration_city = $registration_ip_metrics[0]['city'];
		}
	}

	if ( isset( $registration_ip_metrics[0]['risk'] ) ) {
		$registration_risk = $registration_ip_metrics[0]['risk'];
	} else {
		$registration_risk = 'N/A';
	}

	$riskr = $registration_risk . '%';
	if ( $registration_risk <= 33 ) {
		$riskr_color = '#91a02b';
		$riskr_bg    = '#f4f7e1';
	} elseif ( $registration_risk >= 34 && $registration_risk <= 65 ) {
		$riskr_color = '#f5a91b';
		$riskr_bg    = '#fef3de';
	} elseif ( $registration_risk >= 66 ) {
		$riskr_color = '#b50000';
		$riskr_bg    = '#ffe8e8';
	}
	if ( 'N/A' === $registration_risk ) {
		$riskr_color = '#000000';
		$riskr_bg    = '#e9e9e9';
		$riskr       = $registration_risk;
	}

	if ( ! empty( $registration_ip_metrics[0]['datetime_blog'] ) ) {
		$reg_time = $registration_ip_metrics[0]['datetime_blog'];
	} else {
		$reg_time = 'Uknown';
	}

	if ( 'user_ip' === $column_name ) {
		$user_ip_html = '';
		if ( '' !== get_user_meta( $user_id, 'last_login_ip', true ) ) {
			$user_ip_html .= '<div class="pvb-user-ip-container" style="background:' . $riskl_bg . ';">';
			$user_ip_html .= '<strong>Last Login IP</strong>';
			$user_ip_html .= '<div class="pvb-users-tooltip-container">';
			$user_ip_html .= '	<span class="dashicons dashicons-clock pvb-users-tooltip-icon"></span>';
			$user_ip_html .= '	<span class="pvb-users-tooltip-content">' . $login_time . '</span>';
			$user_ip_html .= '</div>';
			$user_ip_html .= '<p class="pvb-last-login-ip">
								<img class="pvb-ip-user-flag" src="' . $login_flag . '" title="' . $last_login_country_full . ' : ' . $last_login_city . '"></img>
								<span class="pvb-ip-address-container">
									<a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'last_login_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'last_login_ip', true ) . '" class="pvb-ip-address-link">' . get_user_meta( $user_id, 'last_login_ip', true ) . '</a>
								</span>
								<a href="#" class="pvb-ip-filter-icon" style="background:' . $riskl_bg . ';" data-ip="' . esc_attr( get_user_meta( $user_id, 'last_login_ip', true ) ) . '" title="Filter users by this IP address">
									<span class="dashicons dashicons-search"></span>
								</a>
							</p>
							<p>Risk: <strong style="color:' . $riskl_color . ';">' . $last_login_risk . '%</strong></p>';
			$user_ip_html .= '</div>';
		} else {
			$user_ip_html .= '<div class="pvb-user-ip-container" style="background:#e9e9e9;">';
			$user_ip_html .= '<strong>Last Login IP</strong>';
			$user_ip_html .= '<p class="pvb-last-login-ip">User Hasn\'t Logged In</p>';
			$user_ip_html .= '</div>';
		}
		if ( '' !== get_user_meta( $user_id, 'signup_ip', true ) && 'none' !== get_user_meta( $user_id, 'signup_ip', true ) && '' === get_user_meta( $user_id, 'registration_ip', true ) ) {
			$user_ip_html .= '<div class="pvb-user-ip-container" style="background:' . $riskr_bg . ';">';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<p class="pvb-registration-ip">
								<a style="font-size: 13px;" href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'signup_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'signup_ip', true ) . '">' . get_user_meta( $user_id, 'signup_ip', true ) . '</a><p style="font-size: 13px;">Risk: <strong>Unknown</strong>
							</p>';
			$user_ip_html .= '</div>';
		} elseif ( '' !== get_user_meta( $user_id, 'registration_ip', true ) ) {
			$user_ip_html .= '<div class="pvb-user-ip-container" style="background:' . $riskr_bg . ';">';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<div class="pvb-users-tooltip-container">';
			$user_ip_html .= '	<span class="dashicons dashicons-clock pvb-users-tooltip-icon"></span>';
			$user_ip_html .= '	<span class="pvb-users-tooltip-content">' . $reg_time . '</span>';
			$user_ip_html .= '</div>';
			$user_ip_html .= '<p class="pvb-registration-ip">
									<img class="pvb-ip-user-flag" src="' . $registration_flag . '" title="' . $registration_country_full . ' : ' . $registration_city . '"></img>
									<span class="pvb-ip-address-container">
										<a href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'registration_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $user_id, 'registration_ip', true ) . '" class="pvb-ip-address-link">' . get_user_meta( $user_id, 'registration_ip', true ) . '</a>
									</span>
									<a href="#" class="pvb-ip-filter-icon" style="background:' . $riskr_bg . ';" data-ip="' . esc_attr( get_user_meta( $user_id, 'registration_ip', true ) ) . '" title="Filter users by this IP address">
										<span class="dashicons dashicons-search"></span>
									</a>
								</p>
								<p>Risk: <strong style="color:' . $riskr_color . ';">' . $riskr . '</strong></p>';
			$user_ip_html .= '</div>';
		} else {
			$user_ip_html .= '<div class="pvb-user-ip-container" style="background:#e9e9e9;">';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<p class="pvb-registration-ip">Not Recorded</p>';
			$user_ip_html .= '</div>';
		}
	}
		return wp_kses_post( $user_ip_html );
}
add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );

/**
 * Add IP address filtering to WordPress user search
 *
 * @since 3.5.5
 * @param array $args The query arguments for WP_User_Query.
 * @return array Modified query arguments.
 */
function pvb_filter_users_by_ip( $args ) {
	// Only modify if there's a search term.
	if ( empty( $args['search'] ) ) {
		return $args;
	}

	$search_term = $args['search'];

	// Remove the '*' wildcard that WordPress adds to search terms.
	$search_term = trim( $search_term, '*' );

	// Check if the search term looks like an IP address (IPv4).
	if ( pvb_is_ip_address( $search_term ) ) {
		// Create meta query to search for IP addresses in user meta.
		$ip_meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => 'registration_ip',
				'value'   => $search_term,
				'compare' => 'LIKE',
			),
			array(
				'key'     => 'last_login_ip',
				'value'   => $search_term,
				'compare' => 'LIKE',
			),
		);

		// Get existing meta query if any
		$existing_meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		// Merge with existing meta query properly.
		if ( ! empty( $existing_meta_query ) && is_array( $existing_meta_query ) ) {
			// If existing meta query has a 'relation' key, use it, otherwise default to 'AND'.
			$relation = isset( $existing_meta_query['relation'] ) ? $existing_meta_query['relation'] : 'AND';

			// Create new meta query structure,
			$new_meta_query = array(
				'relation' => $relation,
			);

			// Add existing meta queries (skip the first element if it's the relation).
			foreach ( $existing_meta_query as $key => $meta_condition ) {
				if ( 'relation' !== $key ) {
					$new_meta_query[] = $meta_condition;
				}
			}

			// Add our IP search meta query.
			$new_meta_query[] = $ip_meta_query;

			$final_meta_query = $new_meta_query;
		} else {
			// No existing meta query, use just our IP search.
			$final_meta_query = $ip_meta_query;
		}

		// Set the final meta query.
		$args['meta_query'] = $final_meta_query;

		// Clear the search to prevent WordPress from doing its own search.
		$args['search'] = '';
		$args['search_columns'] = array();
	}

	return $args;
}
add_filter( 'users_list_table_query_args', 'pvb_filter_users_by_ip', 20 );



/**
 * Check if a string looks like an IP address
 *
 * @since 3.5.5
 * @param string $string The string to check.
 * @return bool True if the string looks like an IP address.
 */
function pvb_is_ip_address( $string ) {
	// Remove any whitespace.
	$string = trim( $string );

	// Check for IPv4 format (basic validation).
	if ( preg_match( '/^(\d{1,3}\.){0,3}\d{1,3}/', $string, $matches ) ) {
		$ip_part = $matches[0];

		// For partial IPs, just check the format.
		if ( substr_count( $ip_part, '.' ) < 3 ) {
			return true;
		}

		// For full IPs, do more strict validation.
		if ( filter_var( $ip_part, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return true;
		}
	}

	return false;
}


/**
 * Show the IP on a profile to admins only
 *
 * @since 1.8.3
 * @param type $profileuser from edit_user_profile and show_user_profile hooks.
 */
function edit_user_profile( $profileuser ) {
	if ( current_user_can( 'manage_options' ) ) {
		$last_login_ip_metrics   = get_user_meta( $profileuser->ID, 'last_login_ip_metrics', false );
		$last_login_country_full = 'Unknown Country';
		$last_login_city         = 'Unknown City';
		$last_login_country      = 'Unknown Country';
		$login_flag              = plugins_url( '../assets/img/country_flags/NO-FLAG.png', __FILE__ );
		if ( isset( $last_login_ip_metrics[0]['country_code'] ) ) {
			$last_login_country = $last_login_ip_metrics[0]['country_code'];
			$login_flag         = plugins_url( '../assets/img/country_flags/' . $last_login_country . '.png', __FILE__ );
			if ( isset( $last_login_ip_metrics[0]['country'] ) ) {
				$last_login_country_full = $last_login_ip_metrics[0]['country'];
			}
			if ( isset( $last_login_ip_metrics[0]['city'] ) ) {
				$last_login_city = $last_login_ip_metrics[0]['city'];
			}
		}

		if ( isset( $last_login_ip_metrics[0]['risk'] ) ) {
			$last_login_risk = $last_login_ip_metrics[0]['risk'];
		} else {
			$last_login_risk = 'N/A';
		}

		if ( $last_login_risk <= 33 ) {
			$riskl_color = '#91a02b';
			$riskl_bg    = '#f4f7e1';
		} elseif ( $last_login_risk >= 34 && $last_login_risk <= 65 ) {
			$riskl_color = '#f5a91b';
			$riskl_bg    = '#fef3de';
		} elseif ( $last_login_risk >= 66 ) {
			$riskl_color = '#b50000';
			$riskl_bg    = '#ffe8e8';
		}
		if ( 'N/A' === $last_login_risk ) {
			$riskl_color = '#000000';
			$riskl_bg    = '#e9e9e9';
		}

		if ( ! empty( $last_login_ip_metrics[0]['datetime_blog'] ) ) {
			$login_time = $last_login_ip_metrics[0]['datetime_blog'];
		} else {
			$login_time = 'Uknown';
		}

		$registration_ip_metrics   = get_user_meta( $profileuser->ID, 'registration_ip_metrics', false );
		$registration_country_full = 'Unknown Country';
		$registration_city         = 'Unknown City';
		$registration_flag         = plugins_url( '../assets/img/country_flags/NO-FLAG.png', __FILE__ );
		if ( isset( $registration_ip_metrics[0]['country_code'] ) ) {
			$registration_country = $registration_ip_metrics[0]['country_code'];
			$registration_flag    = plugins_url( '../assets/img/country_flags/' . $registration_country . '.png', __FILE__ );
			if ( isset( $registration_ip_metrics[0]['country'] ) ) {
				$registration_country_full = $registration_ip_metrics[0]['country'];
			}
			if ( isset( $registration_ip_metrics[0]['city'] ) ) {
				$registration_city = $registration_ip_metrics[0]['city'];
			}
		}

		if ( isset( $registration_ip_metrics[0]['risk'] ) ) {
			$registration_risk = $registration_ip_metrics[0]['risk'];
		} else {
			$registration_risk = 'N/A';
		}

		$riskr = $registration_risk . '%';
		if ( $registration_risk <= 33 ) {
			$riskr_color = '#91a02b';
			$riskr_bg    = '#f4f7e1';
		} elseif ( $registration_risk >= 34 && $registration_risk <= 65 ) {
			$riskr_color = '#f5a91b';
			$riskr_bg    = '#fef3de';
		} elseif ( $registration_risk >= 66 ) {
			$riskr_color = '#b50000';
			$riskr_bg    = '#ffe8e8';
		}
		if ( 'N/A' === $registration_risk ) {
			$riskr_color = '#000000';
			$riskr_bg    = '#e9e9e9';
			$riskr       = $registration_risk;
		}

		if ( ! empty( $registration_ip_metrics[0]['datetime_blog'] ) ) {
			$reg_time = $registration_ip_metrics[0]['datetime_blog'];
		} else {
			$reg_time = 'Uknown';
		}

		$user_ip_html = '';
		if ( '' !== get_user_meta( $profileuser->ID, 'last_login_ip', true ) ) {
			$user_ip_html .= '<br /><div class="pvb-user-ip-container" style="background:' . $riskl_bg . ';">';
			$user_ip_html .= '<strong>Last Login IP</strong>';
			$user_ip_html .= '<div class="pvb-users-tooltip-container">';
			$user_ip_html .= '	<span class="dashicons dashicons-clock pvb-users-tooltip-icon"></span>';
			$user_ip_html .= '	<span class="pvb-users-tooltip-content">' . $login_time . '</span>';
			$user_ip_html .= '</div>';
			$user_ip_html .= '<p class="pvb-last-login-ip"><img class="pvb-ip-user-flag" src="' . $login_flag . '" title="' . $last_login_country_full . ' : ' . $last_login_city . '"></img> <a href="https://proxycheck.io/threats/' . get_user_meta( $profileuser->ID, 'last_login_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $profileuser->ID, 'last_login_ip', true ) . '" >' . get_user_meta( $profileuser->ID, 'last_login_ip', true ) . '</a></p><p>Risk: <strong style="color:' . $riskl_color . ';">' . $last_login_risk . '%</strong></p>';
			$user_ip_html .= '</div>';
		} else {
			$user_ip_html .= '<br /><div class="pvb-user-ip-container" style="background:#e9e9e9;">';
			$user_ip_html .= '<strong>Last Login IP</strong>';
			$user_ip_html .= '<p class="pvb-last-login-ip">User Hasn\'t Logged In</p>';
			$user_ip_html .= '</div>';
		}
		if ( '' !== get_user_meta( $profileuser->ID, 'signup_ip', true ) && 'none' !== get_user_meta( $profileuser->ID, 'signup_ip', true ) && '' === get_user_meta( $profileuser->ID, 'registration_ip', true ) ) {
			$user_ip_html .= '<br /><div class="pvb-user-ip-container" style="background:' . $riskr_bg . ';"';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<p class="pvb-registration-ip"><a style="font-size: 13px;" href="https://proxycheck.io/threats/' . get_user_meta( $user_id, 'signup_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $profileuser->ID, 'signup_ip', true ) . '">' . get_user_meta( $profileuser->ID, 'signup_ip', true ) . '</a><p style="font-size: 13px;">Risk: <strong>Unknown</strong></p>';
			$user_ip_html .= '</div>';
		} elseif ( '' !== get_user_meta( $profileuser->ID, 'registration_ip', true ) ) {
			$user_ip_html .= '<br /><div class="pvb-user-ip-container" style="background:' . $riskr_bg . ';">';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<div class="pvb-users-tooltip-container">';
			$user_ip_html .= '	<span class="dashicons dashicons-clock pvb-users-tooltip-icon"></span>';
			$user_ip_html .= '	<span class="pvb-users-tooltip-content">' . $reg_time . '</span>';
			$user_ip_html .= '</div>';
			$user_ip_html .= '<p class="pvb-registration-ip"><img class="pvb-ip-user-flag" src="' . $registration_flag . '" title="' . $registration_country_full . ' : ' . $registration_city . '"></img> <a href="https://proxycheck.io/threats/' . get_user_meta( $profileuser->ID, 'registration_ip', true ) . '" target="_blank" title="IP Threat Information for: ' . get_user_meta( $profileuser->ID, 'registration_ip', true ) . '">' . get_user_meta( $profileuser->ID, 'registration_ip', true ) . '</a></p><p>Risk: <strong style="color:' . $riskr_color . ';">' . $riskr . '</strong></p>';
			$user_ip_html .= '</div>';
		} else {
			$user_ip_html .= '<br /><div class="pvb-user-ip-container" style="background:#e9e9e9;">';
			$user_ip_html .= '<strong>Registration IP</strong>';
			$user_ip_html .= '<p class="pvb-registration-ip">Not Recorded</p>';
			$user_ip_html .= '</div>';
		}
		echo $user_ip_html;
	}
}
add_action( 'edit_user_profile', 'edit_user_profile', 10, 1 );
add_action( 'show_user_profile', 'edit_user_profile', 10, 1 );
