<?php

namespace Roshangara\Parser;

use Roshangara\Errorable\Errorable;

/**
 * Class Parser
 * @package Roshangara\Parser
 */
class Parser
{
    use Errorable;

    /**
     * Parsed data
     * @var array
     */
    protected $result = [];

    /**
     * Convert xml to array
     *
     * @param string $xml
     * @return array|mixed
     */
    public function fromXml(string $xml): array
    {
        if (!$this->isEmptyValue($xml)) {

            libxml_use_internal_errors(true);

            // convert string to xml object
            $result = simplexml_load_string("$xml", 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($result) {
                return $this->fromClass($result);

            } else {

                foreach (libxml_get_errors() as $error) {

                    // if don`t have container add that`s
                    if ($error->code == 5) {
                        $this->errors = [];

                        // append container and parse again
                        return $this->fromXml("<DATA>$xml</DATA>");

                    } else
                        $this->setError($error->code, $error->message);
                }
            }
        }

        return $this->result;
    }

    /**
     * Check value not empty
     *
     * @param $value
     * @return bool
     */
    protected function isEmptyValue($value): bool
    {
        if ($value)
            return false;
        else
            $this->setError(0, 'Empty Input', debug_backtrace()[1]);

        return true;
    }

    /**
     * Convert class to array
     *
     * @param $xsd
     * @return array|mixed
     */
    public function fromXSD($xsd): array
    {
        if (!$this->isEmptyValue($xsd)) {
            return $this->fromXml($xsd->output);
        }

        return $this->result;

    }

    /**
     * Convert class to array
     *
     * @param $class
     * @return array|mixed
     */
    public function fromClass($class): array
    {
        if (!$this->isEmptyValue($class)) {
            return $this->fromJson(json_encode($class));
        }

        return $this->result;
    }

    /**
     * Convert json to array
     *
     * @param string $json
     * @return array|mixed
     */
    public function fromJson(string $json): array
    {
        if (!$this->isEmptyValue($json)) {

            if ($decode = json_decode(trim($json), true) and json_last_error() == JSON_ERROR_NONE)
                $this->result = $decode;
            else
                $this->setError(json_last_error(), json_last_error_msg());
        }

        return (array)$this->result;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this->result;
    }
}
