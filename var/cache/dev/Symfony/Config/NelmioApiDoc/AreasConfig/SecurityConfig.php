<?php

namespace Symfony\Config\NelmioApiDoc\AreasConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class SecurityConfig 
{
    private $type;
    private $scheme;
    private $in;
    private $name;
    private $description;
    private $openIdConnectUrl;
    private $_usedProperties = [];
    private $_extraKeys;

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function scheme($value): static
    {
        $this->_usedProperties['scheme'] = true;
        $this->scheme = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function in($value): static
    {
        $this->_usedProperties['in'] = true;
        $this->in = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function description($value): static
    {
        $this->_usedProperties['description'] = true;
        $this->description = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function openIdConnectUrl($value): static
    {
        $this->_usedProperties['openIdConnectUrl'] = true;
        $this->openIdConnectUrl = $value;

        return $this;
    }

    public function __construct(array $value = [])
    {
        if (array_key_exists('type', $value)) {
            $this->_usedProperties['type'] = true;
            $this->type = $value['type'];
            unset($value['type']);
        }

        if (array_key_exists('scheme', $value)) {
            $this->_usedProperties['scheme'] = true;
            $this->scheme = $value['scheme'];
            unset($value['scheme']);
        }

        if (array_key_exists('in', $value)) {
            $this->_usedProperties['in'] = true;
            $this->in = $value['in'];
            unset($value['in']);
        }

        if (array_key_exists('name', $value)) {
            $this->_usedProperties['name'] = true;
            $this->name = $value['name'];
            unset($value['name']);
        }

        if (array_key_exists('description', $value)) {
            $this->_usedProperties['description'] = true;
            $this->description = $value['description'];
            unset($value['description']);
        }

        if (array_key_exists('openIdConnectUrl', $value)) {
            $this->_usedProperties['openIdConnectUrl'] = true;
            $this->openIdConnectUrl = $value['openIdConnectUrl'];
            unset($value['openIdConnectUrl']);
        }

        $this->_extraKeys = $value;

    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['scheme'])) {
            $output['scheme'] = $this->scheme;
        }
        if (isset($this->_usedProperties['in'])) {
            $output['in'] = $this->in;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['description'])) {
            $output['description'] = $this->description;
        }
        if (isset($this->_usedProperties['openIdConnectUrl'])) {
            $output['openIdConnectUrl'] = $this->openIdConnectUrl;
        }

        return $output + $this->_extraKeys;
    }

    /**
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function set(string $key, mixed $value): static
    {
        $this->_extraKeys[$key] = $value;

        return $this;
    }

}
