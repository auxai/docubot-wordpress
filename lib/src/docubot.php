<?php
/*
Copyright (C)  2017, 1LAW Legal Technologies, LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace OneLaw;

class PreviewMessageResponse {

    /**
     * PreviewMessageResponseData
     */
    public $data;
    /**
     * DocubotMessageResponseMeta
     */
    public $meta;

}

class PreviewMessageResponseData {

    /**
     * array<string>
     */
    public $messages;
    /**
     * bool
     */
    public $complete;
    /**
     * array
     */
    public $variables;

}

class DocubotMessageResponse {

    /**
     * DocubotMessageResponseData
     */
    public $data;
    /**
     * DocubotMessageResponseMeta
     */
    public $meta;

}

class DocubotMessageResponseData {

    /**
     * array<string>
     */
    public $messages;
    /**
     * bool
     */
    public $complete;

}

class DocubotMessageResponseMeta {

    /**
     * string
     */
    public $threadId;
    /**
     * string
     */
    public $userId;
    /**
     * array
     */
    public $messageMetaData;

}

class DocubotURLResponse {

    /**
     * DocubotURLResponseData
     */
    public $data;
    /**
     * Associative Array
     */
    public $meta;

}

class DocubotURLResponseData {

    /**
     * string
     */
    public $url;

}

class DocubotError {

    /**
     * array<string>
     */
    public $errors;

}

/**
 * The Docubot class has convenience methods that assist in communicating with the Docubot API.
 */
class Docubot {

    private $APIKey;
    private $APISecret;
    private $APIURLBase;

    /**
     * Creates a new Docubot Instance.
     *
     * @param string $key The API Key for this Docubot Instance.
     * @param string $secret The API Secret for this Docubot Instance.
     * @param string $urlBase The optional base URL for Docubot (if not using live server)
     *  (Do not include a trailing '/')
     */
    public function __construct( $key, $secret, $urlBase = "https://docubotapi.1law.com" ) {

        $this->APIKey = $key;
        $this->APISecret = $secret;
        $this->APIURLBase = $urlBase;

    }

    /**
     * Send a preview message to Docubot.
     *
     * @param string $message The message to send to docubot.
     * @param array $variables The array of variables for this current conversation
     * @param array $docTree The document tree to use to process this message
     *
     * @return DocubotMessageResponse|DocubotError
     */
    public function send_preview_message( $message, $variables, $docTree ) {

        $ch = curl_init( $this->APIURLBase . '/api/v1/preview' );
        $data = [ 'message' => $message, 'docTree' => $docTree, 'variables' => $variables ];
        $data = json_encode( $data );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_USERPWD, $this->APIKey . ':' . $this->APISecret );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        if ( $result === false) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => curl_error( $ch ) ];
            curl_close( $ch );
            return $err;

        }
        $info = curl_getinfo( $ch );
        curl_close( $ch );
        $result = json_decode( $result, true );
        if ( $info['http_code'] < 200 || $info['http_code'] > 299 ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => $result['errors'] ];
            return $err;

        }
        $rData = $result['data'];
        $docuData = new PreviewMessageResponseData();
        $docuData->messages = $rData['messages'];
        $docuData->complete = $rData['complete'];
        $docuData->variables = $rData['variables'];
        $rMeta = $result['meta'];
        $docuMeta = new DocubotMessageResponseMeta();
        $docuMeta->threadId = $rMeta['threadId'];
        $docuMeta->userId = $rMeta['userId'];
        $docuMeta->messageMetaData = $rMeta['messageMetaData'];
        $docuResponse = new PreviewMessageResponse();
        $docuResponse->data = $docuData;
        $docuResponse->meta = $docuMeta;
        return $docuResponse;

    }

    /**
     * Retrieve a preview document from docubot.
     *
     * @param array $variables The variables to use to build the document.
     * @param string $document The document to build.
     * @param FilePointer $fp The file pointer resource to write the resulting document data to.
     *
     * @return null|DocubotError
     */
    public function get_preview_document( $variables, $document, $fp ) {

        $ch = curl_init( $this->APIURLBase . '/api/v1/preview/doc' );
        $data = [ 'document' => $document, 'variables' => $variables ];
        $data = json_encode( $data );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_USERPWD, $this->APIKey . ':' . $this->APISecret );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ) );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        $result = curl_exec( $ch );
        if ( $result === false ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => curl_error( $ch ) ];
            curl_close( $ch );
            return $err;

        }
        $info = curl_getinfo( $ch );
        curl_close( $ch );
        if ( $info['http_code'] < 200 || $info['http_code'] > 299 ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => [ 'Invalid Response Code' ] ];
            return $err;

        }

        return;

    }

    /**
     * Send a message to Docubot.
     *
     * @param string $message The message to send to docubot.
     * @param string $thread The ID of the thread to send the message for.
     *  (blank if starting a new thread)
     * @param string $sender The ID of the user who is sending the message.
     *  (blank if starting a new thread, or a new user)
     *
     * @return DocubotMessageResponse|DocubotError
     */
    public function send_message( $message, $thread = "", $sender = "" ) {

        $ch = curl_init( $this->APIURLBase . '/api/v1/docubot' );
        $data = [ 'message' => $message, 'thread' => $thread, 'sender' => $sender ];
        $data = json_encode( $data );
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $ch, CURLOPT_USERPWD, $this->APIKey . ':' . $this->APISecret );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json' ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        if ( $result === false) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => curl_error( $ch ) ];
            curl_close( $ch );
            return $err;

        }
        $info = curl_getinfo( $ch );
        curl_close( $ch );
        $result = json_decode( $result, true );
        if ( $info['http_code'] < 200 || $info['http_code'] > 299 ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => $result['errors'] ];
            return $err;

        }
        $rData = $result['data'];
        $docuData = new DocubotMessageResponseData();
        $docuData->messages = $rData['messages'];
        $docuData->complete = $rData['complete'];
        $rMeta = $result['meta'];
        $docuMeta = new DocubotMessageResponseMeta();
        $docuMeta->threadId = $rMeta['threadId'];
        $docuMeta->userId = $rMeta['userId'];
        $docuMeta->messageMetaData = $rMeta['messageMetaData'];
        $docuResponse = new DocubotMessageResponse();
        $docuResponse->data = $docuData;
        $docuResponse->meta = $docuMeta;
        return $docuResponse;
    }

    /**
     * Retrieve a document from docubot.
     *
     * @param string $thread The ID of the thread to retrieve the document for.
     * @param string $user The ID of the user for whom to get the document.
     * @param FilePointer $fp The file pointer resource to write the resulting document data to.
     *
     * @return null|DocubotError
     */
    public function get_document( $thread, $user, $fp ) {

        $ch = curl_init( $this->APIURLBase . '/api/v1/docubot/' . $thread . '/doc/download?' . http_build_query( ['user' => $user] ) );
        curl_setopt( $ch, CURLOPT_USERPWD, $this->APIKey . ':' . $this->APISecret );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/x-www-form-urlencoded' ) );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        $result = curl_exec( $ch );
        if ( $result === false ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => curl_error( $ch ) ];
            curl_close( $ch );
            return $err;

        }
        $info = curl_getinfo( $ch );
        curl_close( $ch );
        if ( $info['http_code'] < 200 || $info['http_code'] > 299 ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => [ 'Invalid response code' ] ];
            return $err;

        }

        return;

    }

    /**
     * Retrieve a document URL from Docubot.
     *
     * @param string $thread The ID of the thread to retrieve the document for.
     * @param string $user The ID of the user for whom to get the document.
     * @param int $exp The number of seconds (from now) the URL should last before it expires. (Max 86400/24hrs Default 43200/12hrs)
     *
     * @return DocubotURLResponse|DocubotError
     */
    public function get_document_url( $thread, $user, $exp = 43200 ) {

        $ch = curl_init( $this->APIURLBase . '/api/v1/docubot/' . $thread . '/doc/url?' . http_build_query( ['user' => $user, 'duration' => $exp] ) );
        curl_setopt( $ch, CURLOPT_USERPWD, $this->APIKey . ':' . $this->APISecret );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/x-www-form-urlencoded' ) );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec( $ch );
        if ( $result === false ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => curl_error( $ch ) ];
            curl_close( $ch );
            return $err;

        }
        $info = curl_getinfo( $ch );
        curl_close( $ch );
        $result = json_decode( $result, true );
        if ( $info['http_code'] < 200 || $info['http_code'] > 299 ) {

            $err = new DocubotError();
            $err->errors = [ 'errors' => $result['errors'] ];
            return $err;

        }
        $rData = $result['data'];
        $docuUrlData = new DocubotURLResponseData();
        $docuUrlData->url = $rData['url'];
        $docuUrl = new DocubotURLResponse();
        $docuUrl->data = $docuUrlData;
        $docuUrl->meta = $result['meta'];
        return $docuUrl;

    }

}
