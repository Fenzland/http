<?php

namespace FenzHTTP;

use FenzHelpers\TGetter;

////////////////////////////////////////////////////////////////

class Response
{
	use TGetter;

	/**
	 * Var handle
	 *
	 * @access protected
	 *
	 * @var    resource
	 */
	protected $handle;

	/**
	 * Var version
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $version= '1.1';

	/**
	 * Var statusCode
	 *
	 * @access protected
	 *
	 * @var    int
	 */
	protected $statusCode= 100;

	/**
	 * Var statusMessage
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $statusMessage= '';

	/**
	 * Var request
	 *
	 * @access protected
	 *
	 * @var    Request
	 */
	protected $request;

	/**
	 * Var headers
	 *
	 * @access protected
	 *
	 * @var    array
	 */
	protected $headers= [];

	/**
	 * Var body
	 *
	 * @access protected
	 *
	 * @var    string
	 */
	protected $body= '';

	/**
	 * Method __construct
	 *
	 * @access public
	 *
	 * @param  Request $request
	 * @param  resource $handle
	 */
	public function __construct( Request$request, $handle )
	{
		$this->request= $request;
		$this->handle= $handle;

		$this->parsMessageLine(trim(fgets($this->handle)));

		while( $headerLine= trim(fgets($this->handle)) ){
			$this->parseHeader($headerLine);
		}

		$this->readBody();
	}

	/**
	 * Method getRequest
	 *
	 * @access public
	 *
	 * @return Request
	 */
	public function getRequest():Request
	{
		return $this->request;
	}

	/**
	 * Method getStatus
	 *
	 * @access public
	 *
	 * @return int
	 */
	public function getStatus():int
	{
		return $this->statusCode;
	}

	/**
	 * Method getStatusCode
	 *
	 * @access public
	 *
	 * @return int
	 */
	public function getStatusCode():int
	{
		return $this->getStatus();
	}

	/**
	 * Method getStatusMessage
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getStatusMessage():string
	{
		return $this->getStatusMessage;
	}

	/**
	 * Method header
	 *
	 * @access public
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function header( string$key=null )
	{
		return ( isset($key) ?
			$this->headers[ucwords( strtolower($key), '-' )]
			:
			$this->getHeaders()
		);
	}

	/**
	 * Method getHeaders
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function getHeaders():array
	{
		return $this->headers;
	}

	/**
	 * Method getBody
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getBody():string
	{
		return $this->body;
	}

	/**
	 * Method getJson
	 *
	 * @access public
	 *
	 * @return mixed
	 */
	public function getJson()
	{
		try{
			return json_decode( $this->body, true );
		}
		catch( \Throwable$e )
		{
			return null;
		}
	}

	/**
	 * Method parsMessageLine
	 *
	 * @access private
	 *
	 * @param  string $messageLine
	 *
	 * @return void
	 */
	private function parsMessageLine( string$messageLine )
	{
		preg_replace_callback( '/^HTTP\\/(\\S+) (\\S+) (.+)$/', function( array$matches ){
			list( $null, $this->version, $this->statusCode, $this->statusMessage )= $matches;
		}, $messageLine );
	}

	/**
	 * Method parseHeader
	 *
	 * @access private
	 *
	 * @param  string $headerLine
	 *
	 * @return void
	 */
	private function parseHeader( string$headerLine )
	{
		list( $key, $value, )= explode(': ', $headerLine);

		$this->headers[ucwords( strtolower($key), '-' )]= $value;
	}

	/**
	 * Method readBody
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function readBody()
	{
		$odd= true;

		while( !feof($this->handle) )
		{
			$line= fgets($this->handle);

			if( substr( $line, -2 )==="\r\n" )
			{
				if( !$odd )
				{
					$this->body.= substr( $line, 0, -2 );
				}

				$odd= !$odd;
			}else{
				$this->body.= $line;
			}
		}
	}

}
