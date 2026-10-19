<?php

class Monica_Encryption {

    private static $cipher = 'aes-256-cbc';

    public static function init() {
        $options_to_encrypt = [
            'monica_client_secret',
            'monica_access_token',
        ];

        foreach ( $options_to_encrypt as $option ) {
            add_filter( "pre_update_option_{$option}", [ __CLASS__, 'encrypt' ], 10, 2 );
            add_filter( "option_{$option}", [ __CLASS__, 'decrypt' ] );
        }
    }

    private static function get_encryption_key() {
        if ( defined( 'MONICA_ENCRYPTION_KEY' ) ) {
            return MONICA_ENCRYPTION_KEY;
        }

        // Generate a persistent key if one doesn't exist
        $key = get_option( 'monica_encryption_key' );
        if ( ! $key ) {
            $key = wp_generate_password( 64, true, true );
            update_option( 'monica_encryption_key', $key );
        }

        return $key;
    }

    public static function encrypt( $value, $old_value = '' ) {
        if ( empty( $value ) ) {
            return $value;
        }

        // Avoid double encryption if the option is already encrypted
        if ( strpos( $value, 'monica_enc_::' ) === 0 ) {
            return $value;
        }

        $key = self::get_encryption_key();
        $iv_length = openssl_cipher_iv_length( self::$cipher );
        $iv = openssl_random_pseudo_bytes( $iv_length );

        $encrypted = openssl_encrypt( $value, self::$cipher, $key, 0, $iv );

        if ( false === $encrypted ) {
            return $value; // Fallback to plain text if encryption fails
        }

        return 'monica_enc_::' . base64_encode( $encrypted . '::' . $iv );
    }

    public static function decrypt( $value ) {
        if ( empty( $value ) || strpos( $value, 'monica_enc_::' ) !== 0 ) {
            return $value; // Not encrypted
        }

        $key = self::get_encryption_key();
        $encoded = substr( $value, 13 ); // Remove 'monica_enc_::'
        $decoded = base64_decode( $encoded );

        if ( false === $decoded || strpos( $decoded, '::' ) === false ) {
            return $value; // Failed to decode
        }

        list( $encrypted_data, $iv ) = explode( '::', $decoded, 2 );

        $decrypted = openssl_decrypt( $encrypted_data, self::$cipher, $key, 0, $iv );

        if ( false === $decrypted ) {
            return ''; // Failed to decrypt, return empty to avoid leaking ciphertext
        }

        return $decrypted;
    }
}
