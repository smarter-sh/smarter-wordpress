<?php
/**
 * Tests of Smarter_Chat_Validation.
 *
 * @package SmarterChat
 */

/**
 * The validators accept what the embed script accepts, and nothing that could escape an attribute or a script.
 */
class ValidationTest extends Smarter_Chat_Test_Case {

	public function test_url_accepts_http_and_https_urls() {
		$this->assertSame( 'https://support.3141-5926-5359.api.example.com/', Smarter_Chat_Validation::url( ' https://support.3141-5926-5359.api.example.com/ ' ) );
		$this->assertSame( 'http://localhost:9357/api/v1/llm-clients/3/', Smarter_Chat_Validation::url( 'http://localhost:9357/api/v1/llm-clients/3/' ) );
	}

	/**
	 * @dataProvider invalid_urls
	 */
	public function test_url_rejects_anything_else( $value ) {
		$this->assertSame( '', Smarter_Chat_Validation::url( $value ) );
	}

	public function invalid_urls() {
		return array(
			'empty'            => array( '' ),
			'not a string'     => array( array( 'https://example.com/' ) ),
			'javascript'       => array( 'javascript:alert(1)' ),
			'data'             => array( 'data:text/html,<script>alert(1)</script>' ),
			'no host'          => array( 'https:///path' ),
			'relative'         => array( '/chat/' ),
			'quote'            => array( 'https://example.com/"><script>alert(1)</script>' ),
			'space'            => array( 'https://example.com/ onload=alert(1)' ),
			'credentials'      => array( 'https://user:secret@example.com/' ),
			'ftp'              => array( 'ftp://example.com/' ),
			'unparseable'      => array( 'http://:80' ),
		);
	}

	public function test_length() {
		$this->assertSame( '600px', Smarter_Chat_Validation::length( '600px', 'x' ) );
		$this->assertSame( '70vh', Smarter_Chat_Validation::length( ' 70vh ', 'x' ) );
		$this->assertSame( '100%', Smarter_Chat_Validation::length( '100%', 'x' ) );
		$this->assertSame( '33.5rem', Smarter_Chat_Validation::length( '33.5rem', 'x' ) );
		$this->assertSame( 'x', Smarter_Chat_Validation::length( '600', 'x' ) );
		$this->assertSame( 'x', Smarter_Chat_Validation::length( '600px;background:red', 'x' ) );
		$this->assertSame( 'x', Smarter_Chat_Validation::length( 'calc(100vh - 1px)', 'x' ) );
		$this->assertSame( 'x', Smarter_Chat_Validation::length( null, 'x' ) );
	}

	public function test_color() {
		$this->assertSame( '#ff5733', Smarter_Chat_Validation::color( '#ff5733', '#000' ) );
		$this->assertSame( '#000', Smarter_Chat_Validation::color( 'red; background:url(x)', '#000' ) );
		$this->assertSame( '#000', Smarter_Chat_Validation::color( 42, '#000' ) );
	}

	public function test_choice() {
		$this->assertSame( 'cdn', Smarter_Chat_Validation::choice( 'cdn', array( 'local', 'cdn' ), 'local' ) );
		$this->assertSame( 'local', Smarter_Chat_Validation::choice( 'elsewhere', array( 'local', 'cdn' ), 'local' ) );
		$this->assertSame( 'local', Smarter_Chat_Validation::choice( 1, array( 'local', '1' ), 'local' ) );
	}

	public function test_text() {
		$this->assertSame( 'Ask us', Smarter_Chat_Validation::text( ' Ask   us ', 'Chat' ) );
		$this->assertSame( 'Chat', Smarter_Chat_Validation::text( '<script>alert(1)</script>', 'Chat' ) );
		$this->assertSame( 'Chat', Smarter_Chat_Validation::text( array(), 'Chat' ) );
		$this->assertSame( 'abc', Smarter_Chat_Validation::text( 'abcdef', 'Chat', 3 ) );
	}

	public function test_api_key() {
		$this->assertSame( 'a1b2_c3-d4.e5:f6', Smarter_Chat_Validation::api_key( ' a1b2_c3-d4.e5:f6 ' ) );
		$this->assertSame( '', Smarter_Chat_Validation::api_key( 'key"><script>' ) );
		$this->assertSame( '', Smarter_Chat_Validation::api_key( null ) );
	}

	public function test_boolean() {
		foreach ( array( true, 1, '1', 'true', 'YES', ' on ' ) as $value ) {
			$this->assertTrue( Smarter_Chat_Validation::boolean( $value ) );
		}
		foreach ( array( false, 0, '0', 'false', 'no', '', null ) as $value ) {
			$this->assertFalse( Smarter_Chat_Validation::boolean( $value ) );
		}
	}
}
