<?php
/**
 * Generate API Key Statistics Page.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = array(
	'div'    => array(
		'class' => array(),
		'id'    => array(),
		'style' => array(),
		'dir'   => array(),
	),
	'a'      => array(
		'href'  => array(),
		'title' => array(),
	),
	'i'      => array(
		'class' => array(),
	),
	'script' => array(
		'type' => array(),
	),
	'form'   => array(
		'class'  => array(),
		'id'     => array(),
		'action' => array(),
		'method' => array(),
		'target' => array(),
	),
	'input'  => array(
		'class' => array(),
		'id'    => array(),
		'name'  => array(),
		'type'  => array(),
		'title' => array(),
		'value' => array(),
	),
	'button' => array(
		'class'   => array(),
		'id'      => array(),
		'type'    => array(),
		'onclick' => array(),
		'name'    => array(),
		'style'   => array(),
	),
	'strong' => array(),
	'h1'     => array(),
	'h2'     => array(
		'class' => array(),
	),
	'h3'     => array(),
	'p'      => array(),
);

/**
 * Generate Cleaner looking big numbers.
 *
 * @param type $n input number.
 */
function number_format_short( $n ) {
	// first strip any formatting.
	$n = ( 0 + str_replace( ',', '', $n ) );

	// is this a number?
	if ( ! is_numeric( $n ) ) {
		return false;
	}

	// now filter it.
	if ( $n > 1000000000000 ) {
		return round( ( $n / 1000000000000 ), 3 ) . ' Trillion';
	} elseif ( $n > 1000000000 ) {
		return round( ( $n / 1000000000 ), 3 ) . ' Billion';
	} elseif ( $n > 1000000 ) {
		return round( ( $n / 1000000 ), 3 ) . ' Million';
	}

	return number_format( $n );
}

/**
 * Get stats from the proxycheck.io Dashboard API.
 */
function pvb_get_proxycheck_api_key_stats() {
	$get_api_key = get_option( 'pvb_proxycheckio_API_Key_field' );

	// Build page HTML.
	$request_args = array(
		'timeout'     => 10,
		'blocking'    => true,
		'httpversion' => '1.1',
	);
	if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) && empty( $get_api_key ) ) {
		// Get the dummy data from the Proxy & VPN Blocker Dummy Pool.
		$response      = wp_remote_get( esc_url( dirname( __DIR__ ) . '/includes/dbg/demo_data/proxycheck.daystat.dummy.json' ) );
		$body          = wp_remote_retrieve_body( $response );
		$api_key_usage = json_decode( $body );
	} else {
		// Get the data from the proxycheck dashboard API.
		$request_usage = wp_remote_get( 'https://proxycheck.io/dashboard/export/usage/?key=' . $get_api_key, $request_args );
		$api_key_usage = json_decode( wp_remote_retrieve_body( $request_usage ) );
	}

	return $api_key_usage;
}

if ( ! empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) || 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) ) {
	$api_key_usage = pvb_get_proxycheck_api_key_stats();
	if ( isset( $api_key_usage->status ) && 'denied' === $api_key_usage->status ) {
		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
		$html .= '<h2></h2>';
		$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		$html .= '<div class="pvberror">' . "\n";
		$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
		$html .= '<div class="pvberrorinside">' . "\n";
		$html .= '<h2>' . __( 'You must enable Dashboard API Access within your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> Dashboard to access this part of Proxy & VPN Blocker', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '</div>';
		echo wp_kses( $html, $allowed_html );
	} elseif ( isset( $api_key_usage->error ) && 'No usage Found.' === $api_key_usage->error ) {
			$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
			$html .= '<h2></h2>';
			$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
			$html .= '<div class="pvberror">' . "\n";
			$html .= '<div class="pvberrortitle">' . __( 'Oops!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
			$html .= '<div class="pvberrorinside">' . "\n";
			$html .= '<h2> An Error with the message "' . $api_key_usage->error . '" was returned by the proxycheck.io API, is your API Key correct?</h2>' . "\n";
			$html .= '</div>' . "\n";
			$html .= '</div>' . "\n";
			$html .= '</div>';
			echo wp_kses( $html, $allowed_html );
	} else {
		// Format and Display usage stats.
		$queries_today          = $api_key_usage->{'Queries Today'};
		$daily_limit            = $api_key_usage->{'Daily Limit'};
		$queries_total          = $api_key_usage->{'Queries Total'};
		$plan_tier              = $api_key_usage->{'Plan Tier'};
		$burst_tokens           = $api_key_usage->{'Burst Tokens Available'};
		$burst_tokens_allowance = $api_key_usage->{'Burst Token Allowance'};
		$burst_tokens_active    = $api_key_usage->{'Burst Token Active'};
		$queries_lifetime       = $api_key_usage->{'Queries Total'};
		$bursts_used            = $burst_tokens_allowance - $burst_tokens;
		$usage_percent          = ( $queries_today * 100 ) / $daily_limit;

		// Set CSS for the color of the day's query count.
		$query_color_id = 'query-normal';
		if ( $usage_percent >= 75 && $usage_percent < 90 ) {
			$query_color_id = 'query-warning';
		} elseif ( $usage_percent >= 90 ) {
			$query_color_id = 'query-critical';
		}

		$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
		$html .= '<h2 class="pvb-wp-notice-fix"></h2>' . "\n";
		$html .= '<div class="pvbareawrap">' . "\n";
		$html .= '	<h1>' . __( 'Your proxycheck.io API Key Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		if ( 'on' === get_option( 'pvb_proxycheckio_dummy_data' ) && empty( get_option( 'pvb_proxycheckio_API_Key_field' ) ) ) {
			$html .= '	<div class="api-info-apikey">API Key: 000000-000000-000000-000000</div>' . "\n";
		} else {
			$html .= '	<div class="api-info-apikey">' . __( 'API Key: ', 'proxy-vpn-blocker' ) . get_option( 'pvb_proxycheckio_API_Key_field' ) . '</div>' . "\n";
		}
		$html .= '<div class="api-info-tier">' . __( 'Plan: ', 'proxy-vpn-blocker' ) . $plan_tier . ' | ' . number_format_short( $daily_limit ) . ' Daily Queries</div>' . "\n";
		$html .= '</div>' . "\n";
		$html .= '<div class="api-info">' . "\n";
		$html .= '	<div class="api-info-col1">' . "\n";
		$html .= '		<h1>API Queries & Burst Token Usage</h1>' . "\n";
		$html .= '		<div class="api-query-block">' . "\n";
		$html .= '			<div class="api-queries">' . "\n";
		$html .= '				<div class="api-info-title-small">Queries Today:</div>' . "\n";
		$html .= '				<div class="api-info-queries-used" id="' . $query_color_id . '"><strong> ' . number_format_short( $queries_today ) . '</strong></div>' . "\n";
		$html .= '				<div class="api-info-title-small"> That\'s ' . round( $usage_percent, 2 ) . '% of your plan\'s daily limit. </div>' . "\n";
		$html .= '			</div>' . "\n";
		$html .= '			<div class="api-bursts">' . "\n";
		$html .= '				<div class="api-info-title-small">Burst Tokens:</div>' . "\n";
		$html .= '				<div class="api-info-bursts"><strong>' . $bursts_used . '</strong> / ' . $burst_tokens_allowance . ' Used</div>' . "\n";
		$html .= '				<div class="api-info-title-small">Lifetime Queries:</div>' . "\n";
		$html .= '				<div class="api-info-bursts">' . number_format_short( $queries_lifetime ) . '</div>' . "\n";
		$html .= '			</div>' . "\n";
		$html .= '		</div>' . "\n";
		$html .= '	</div>' . "\n";
		$html .= '	<div class="api-info-col2">' . "\n";
		$html .= '		<h1>Proxy & VPN Blocker Analysis</h1>' . "\n";

		if ( $usage_percent < 75 ) {
			$html .= '<p>Proxy & VPN Blocker has determined that based on your current Query and Burst Token usage, no actions are required.</p>' . "\n";
		} elseif ( $usage_percent >= 75 && $usage_percent < 90 ) {
			$html .= '<p>Over <strong>75%</strong> of your Queries have been used up, today.</p>' . "\n";
		} elseif ( $usage_percent >= 90 && $usage_percent < 100 ) {
			$html .= '<p>Over <strong>90%</strong> of your Queries have been used up, today. You have <strong>' . $burst_tokens . '</strong> Burst Token(s) Available.</p>' . "\n";
			if ( 0 === $burst_tokens ) {
				$html .= '<p>It is important that you keep an eye on your query usage. You have no Burst Tokens left this month!</p>' . "\n";
			} else {
				$html .= '<p>It is recommended that you keep an eye on your query usage. A Burst Token may be used soon.</p>' . "\n";
			}
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently nearing your daily limit based on the graph below, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently nearing your daily limit based on the graph below, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted plans are available from the <a href="https://proxyvpnblocker.com/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		} elseif ( $usage_percent > 100 && ! empty( $burst_tokens ) ) {
			$html .= '<p>Over <strong>100%</strong> of your Queries have been used up, today. A Burst Token has been consumed, increasing your limit by 5x for today only!</p>' . "\n";
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently nearing, or hitting your daily limit based on the graph below, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently nearing, or hitting your daily limit based on the graph below, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted plans are available from the <a href="https://proxyvpnblocker.com/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		} elseif ( 100 === $usage_percent && empty( $burst_tokens ) ) {
			$html .= '<p><strong>100%</strong> of your Queries have been used up, today. You have 0 Burst Tokens left this month and queries are no longer being answered until the daily reset.</p>' . "\n";
			if ( 'Paid' === $plan_tier ) {
				$html .= '<p>If you are consistently hitting your daily limit based on the graph below, or using Burst Tokens, then you may need a higher tier plan</p>' . "\n";
			} else {
				$html .= '<p>If you are consistently hitting your daily limit based on the graph below, or using Burst Tokens, then you may need a paid plan.</p>' . "\n";
			}
			$html .= '<p>Discounted Plans are available from the <a href="https://proxyvpnblocker.com/plan-donate/" target="_blank">Proxy & VPN Blocker Site</a>.</p>' . "\n";
		}

		$html .= '	</div>' . "\n";
		$html .= '</div>' . "\n";

		$html .= '<div class="pvbareawrap">' . "\n";
		$html .= '<h1>' . __( 'API Key Queries: Past Month', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
		echo wp_kses( $html, $allowed_html );

				// Month stats graph.
				$html2 = '<script type="text/javascript">
				am4core.ready(function() {
					// Themes begin
					function am4themes_myTheme(target) {
						if (target instanceof am4core.InterfaceColorSet) {
							target.setFor("secondaryButton", am4core.color("#5ba7cb"));
							target.setFor("secondaryButtonHover", am4core.color("#5ba7cb").lighten(-0.2));
							target.setFor("secondaryButtonDown", am4core.color("#5ba7cb").lighten(-0.2));
							target.setFor("secondaryButtonActive", am4core.color("#5ba7cb").lighten(-0.2));
							target.setFor("secondaryButtonText", am4core.color("#FFFFFF"));
							target.setFor("secondaryButtonStroke", am4core.color("#467B88"));
							target.setFor("text", am4core.color("#8C929A"));
							target.setFor("alternativeText", am4core.color("#8C929A"));
						}
					  }
					am4core.useTheme(am4themes_animated);
					am4core.useTheme(am4themes_myTheme);
					// Themes end
				
					// Create chart instance
					var chart = am4core.create("amchartAPImonth", am4charts.XYChart);
				
					// Increase contrast by taking evey second color
					chart.colors.step = 2;
				
					// Add data 
					chart.dataSource.url = "' . get_site_url() . '/wp-json/proxy-vpn-blocker-stats/v1/month-stats?key=' . get_option( 'pvb_proxycheckio_API_Key_field' ) . '";
				
					// Create Category Axis
					var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
					categoryAxis.dataFields.category = "days";
					categoryAxis.title.text = "Date";
		
					// Create value axis
					var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
					valueAxis.title.text = "Queries";
					valueAxis.renderer.minLabelPosition = 0.01;
		
					// Create series
					var series1 = chart.series.push(new am4charts.LineSeries());
					series1.dataFields.valueY = "proxies";
					series1.dataFields.categoryX = "days";
					series1.name = "Proxies";
					series1.strokeWidth = 2.5;
					series1.tensionX = 0.8;
					series1.tooltipText = " {name}: {valueY}";
					series1.bullets.push(new am4charts.CircleBullet());
		
					var series2 = chart.series.push(new am4charts.LineSeries());
					series2.dataFields.valueY = "vpns";
					series2.dataFields.categoryX = "days";
					series2.name = "VPNs";
					series2.strokeWidth = 2.5;
					series2.tensionX = 0.8;
					series2.tooltipText = " {name}: {valueY}";
					series2.bullets.push(new am4charts.CircleBullet());
				
					var series3 = chart.series.push(new am4charts.LineSeries());
					series3.dataFields.valueY = "undetected";
					series3.dataFields.categoryX = "days";
					series3.name = "Undetected";
					series3.strokeWidth = 2.5;
					series3.tensionX = 0.8;
					series3.tooltipText = " {name}: {valueY}";
					series3.bullets.push(new am4charts.CircleBullet());
				
					var series4 = chart.series.push(new am4charts.LineSeries());
					series4.dataFields.valueY = "disposable\ emails";
					series4.dataFields.categoryX = "days";
					series4.name = "Disposable Emails";
					series4.strokeWidth = 2.5;
					series4.tensionX = 0.8;
					series4.tooltipText = " {name}: {valueY}";
					series4.bullets.push(new am4charts.CircleBullet());
		
					var series5 = chart.series.push(new am4charts.LineSeries());
					series5.dataFields.valueY = "reusable\ emails";
					series5.dataFields.categoryX = "days";
					series5.name = "Reusable Emails";
					series5.strokeWidth = 2.5;
					series5.tensionX = 0.8;
					series5.tooltipText = " {name}: {valueY}";
					series5.bullets.push(new am4charts.CircleBullet());
		
					var series6 = chart.series.push(new am4charts.LineSeries());
					series6.dataFields.valueY = "refused\ queries";
					series6.dataFields.categoryX = "days";
					series6.name = "Refused Queries";
					series6.strokeWidth = 2.5;
					series6.tensionX = 0.8;
					series6.tooltipText = " {name}: {valueY}";
					series6.bullets.push(new am4charts.CircleBullet());
				
					var series7 = chart.series.push(new am4charts.LineSeries());
					series7.dataFields.valueY = "custom\ rules";
					series7.dataFields.categoryX = "days";
					series7.name = "Custom Rules";
					series7.strokeWidth = 2.5;
					series7.tensionX = 0.8;
					series7.tooltipText = " {name}: {valueY}";
					series7.bullets.push(new am4charts.CircleBullet());
		
					var series8 = chart.series.push(new am4charts.LineSeries());
					series8.dataFields.valueY = "blacklisted";
					series8.dataFields.categoryX = "days";
					series8.name = "Blacklisted";
					series8.strokeWidth = 2.5;
					series8.tensionX = 0.8;
					series8.tooltipText = " {name}: {valueY}";
					series8.bullets.push(new am4charts.CircleBullet());
		
					// Add chart cursor
					chart.cursor = new am4charts.XYCursor();
					chart.cursor.behavior = "zoomX";
		
		
					let hs1 = series1.segments.template.states.create("hover")
					hs1.properties.strokeWidth = 5;
					series1.segments.template.strokeWidth = 1;
		
					let hs2 = series2.segments.template.states.create("hover")
					hs2.properties.strokeWidth = 5;
					series2.segments.template.strokeWidth = 1;
		
					let hs3 = series3.segments.template.states.create("hover")
					hs3.properties.strokeWidth = 5;
					series3.segments.template.strokeWidth = 1;
		
					let hs4 = series4.segments.template.states.create("hover")
					hs4.properties.strokeWidth = 5;
					series4.segments.template.strokeWidth = 1;
		
					let hs5 = series5.segments.template.states.create("hover")
					hs5.properties.strokeWidth = 5;
					series5.segments.template.strokeWidth = 1;
		
					let hs6 = series6.segments.template.states.create("hover")
					hs6.properties.strokeWidth = 5;
					series6.segments.template.strokeWidth = 1;
		
					let hs7 = series7.segments.template.states.create("hover")
					hs7.properties.strokeWidth = 5;
					series7.segments.template.strokeWidth = 1;
		
					let hs8 = series8.segments.template.states.create("hover")
					hs8.properties.strokeWidth = 5;
					series8.segments.template.strokeWidth = 1;
		
		
		
					// Add legend
					chart.legend = new am4charts.Legend();
		
				}); // end am4core.ready()
				</script>
				';
		$html2        .= '<div id="amchartAPImonth" style="width: 100%; height: 450px;"></div>' . "\n";
		$html2        .= '</div>' . "\n";
		echo wp_kses( $html2, $allowed_html );
	}
} else {
	$html  = '<div class="wrap" id="' . $this->parent->_token . '_statistics" dir="ltr">' . "\n";
	$html .= '<h1>' . __( 'Proxy &amp; VPN Blocker proxycheck.io Statistics', 'proxy-vpn-blocker' ) . '</h1>' . "\n";
	$html .= '<div class="pvberror">' . "\n";
	$html .= '<div class="pvberrortitle">' . __( 'Oops - Your proxycheck.io API Key is missing!', 'proxy-vpn-blocker' ) . '</div>' . "\n";
	$html .= '<div class="pvberrorinside">' . "\n";
	$html .= '<h2>' . __( 'Please set your <a href="https://proxycheck.io" target="_blank">proxycheck.io</a> API Key in PVB Settings to be able to use this page!', 'proxy-vpn-blocker' ) . '</h2>' . "\n";
	$html .= '<h3>' . __( 'This page will display statistics about your API Key queries and recent detections.', 'proxy-vpn-blocker' ) . '</h3>' . "\n";
	$html .= '</div>' . "\n";
	$html .= '</div>';
	echo wp_kses( $html, $allowed_html );
}
