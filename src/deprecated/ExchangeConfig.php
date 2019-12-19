<?php


namespace VA\Currency;


/**
 * @deprecated newer implementation uses variadic parameters
 */
class ExchangeConfig implements ExchangeConfigInterface
{
    /** @var array */
    private $attributes = [];


    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $val) {
            $this->__set($key, $val);
        }
    }


    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }


    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

}
