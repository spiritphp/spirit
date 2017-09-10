<?php

namespace Spirit\Services;

use Spirit\Engine;
use Spirit\Structure\Single;

class Crypt extends Single
{
    protected $key = null;
    protected $cipher = null;

    public function __construct($key = null, $cipher = 'AES-256-CBC')
    {
        if (!$key) {
            $key = Engine::cfg()->appKey;
        }

        if (1 || static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new \Exception('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');

        return ($cipher === 'AES-128-CBC' && $length === 16) ||
               ($cipher === 'AES-256-CBC' && $length === 32);
    }

    public static function encrypt($value)
    {
        return static::getInstance()
            ->encrypting($value);
    }

    /**
     * @param $value
     * @param bool $serialize
     * @return string
     * @throws \Exception
     */
    public function encrypting($value, $serialize = true)
    {
        $iv = random_bytes(16);

        $value = \openssl_encrypt($serialize ? serialize($value) : $value, $this->cipher, $this->key, 0, $iv);

        if ($value === false) {
            throw new \Exception('Could not encrypt the data.');
        }

        // Once we have the encrypted value we will go ahead base64_encode the input
        // vector and create the MAC for the encrypted value so we can verify its
        // authenticity. Then, we'll JSON encode the data in a "payload" array.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        if (!is_string($json)) {
            throw new \Exception('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string $iv
     * @param  string $value
     * @return string
     */
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv . $value, $this->key);
    }

    public static function decrypt($payload)
    {
        return static::getInstance()
            ->decrypting($payload);
    }

    /**
     * @param $payload
     * @param bool $unserialize
     * @return mixed|string
     * @throws \Exception
     */
    public function decrypting($payload, $unserialize = true)
    {
        if (!$payload) return null;

        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = \openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $iv);

        if ($decrypted === false) {
            throw new \Exception('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * @param $payload
     * @return mixed
     * @throws \Exception
     */
    protected function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (!$this->validPayload($payload)) {
            throw new \Exception('The payload is invalid.');
        }

        if (!$this->validMac($payload)) {
            throw new \Exception('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed $payload
     * @return bool
     */
    protected function validPayload($payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        $calculated = $this->calculateMac($payload, $bytes = random_bytes(16));

        return hash_equals(hash_hmac('sha256', $payload['mac'], $bytes, true), $calculated);
    }

    /**
     * Calculate the hash of the given payload.
     *
     * @param  array $payload
     * @param  string $bytes
     * @return string
     */
    protected function calculateMac($payload, $bytes)
    {
        return hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true);
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}