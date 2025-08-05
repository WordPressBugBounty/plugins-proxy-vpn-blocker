<?php
/**
 * This file contains the encryption class for the Proxy & VPN Blocker plugin.
 *
 * @package Proxy & VPN Blocker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for encrypting and decrypting API keys using AES-256-CBC.
 */
class PVB_API_Key_Encryption {
	/**
	 * Generates a secure encryption key based on WordPress salts.
	 *
	 * @return string The encryption key.
	 */
	private static function get_encryption_key() {
		return hash( 'sha256', wp_salt( 'auth' ) . wp_salt( 'secure_auth' ) );
	}

	/**
	 * Encrypts the given data using AES-256-CBC encryption.
	 *
	 * @param string $data The data to encrypt.
	 * @return string The encrypted data, base64 encoded.
	 */
	public static function encrypt( $data ) {
		if ( empty( $data ) ) {
			return '';
		}

		$key       = self::get_encryption_key();
		$iv        = openssl_random_pseudo_bytes( 16 );
		$encrypted = openssl_encrypt( $data, 'AES-256-CBC', $key, 0, $iv );

		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypts the given encrypted data using AES-256-CBC decryption.
	 *
	 * @param string $encrypted_data The encrypted data, base64 encoded.
	 * @return string The decrypted data.
	 */
	public static function decrypt( $encrypted_data ) {
		if ( empty( $encrypted_data ) ) {
			return ''; // Return empty if no data is provided.
		}

		try {
			$data      = base64_decode( $encrypted_data );
			$key       = self::get_encryption_key();
			$iv        = substr( $data, 0, 16 );
			$encrypted = substr( $data, 16 );

			return openssl_decrypt( $encrypted, 'AES-256-CBC', $key, 0, $iv );
		} catch ( Exception $e ) {
			return ''; // Return empty if decryption fails.
		}
	}

	/**
	 * Checks if the given data is encrypted.
	 *
	 * @param string $data The data to check.
	 * @return bool True if the data is encrypted, false otherwise.
	 */
	public static function is_encrypted( $data ) {
		// Simple check - encrypted data will be base64 and much longer.
		return ! empty( $data ) && base64_encode( base64_decode( $data, true ) ) === $data && strlen( $data ) > 50;
	}
}
