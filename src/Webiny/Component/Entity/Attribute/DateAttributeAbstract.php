<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;
use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObjectException;

/**
 * DateAttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class DateAttributeAbstract extends AttributeAbstract
{

    protected $attributeFormat = 'Y-m-d H:i:s';

    protected $autoUpdate = false;

    public function getDbValue()
    {
        if ($this->isEmpty($this->value)) {
            $this->setDefaultValueInternal();
        }

        if ($this->autoUpdate) {
            $this->setValue(date($this->attributeFormat));
        }

        return new \MongoDate(strtotime($this->getValue()));
    }

    /**
     * Set auto update on or off<br>
     * If true, will update the attribute value with current date/datetime each time it's inserted into DB
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setAutoUpdate($flag = true)
    {
        $this->autoUpdate = $flag;

        return $this;
    }

    public function getToArrayValue()
    {
        return $this->formatValue(parent::getValue());
    }

    public function setValue($value = null)
    {
        if ($this->isInstanceOf($value, '\MongoDate')) {
            if ($value->sec == 0) {
                return parent::setValue(null);
            }
            $value = (new DateTimeObject($value->sec))->format($this->attributeFormat);
        }

        if ($this->isInstanceOf($value, '\Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject')) {
            $value = $value->format($this->attributeFormat);
        }

        if ($value == 'now') {
            $value = date($this->attributeFormat);
        }

        return parent::setValue($value);
    }

    public function getValue($asDateTimeObject = false)
    {
        if($asDateTimeObject){
            return new DateTimeObject(parent::getValue());
        }
        return $this->formatValue(parent::getValue());
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    public function validate(&$value)
    {
        if ($this->isInstanceOf($value, '\Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject')) {
            $value = $value->format($this->attributeFormat);
        }
        if ($this->isInstanceOf($value, '\MongoDate')) {
            if ($value->sec == 0) {
                return $this;
            }
            $value = (new DateTimeObject($value->sec))->format($this->attributeFormat);
        }
        try {
            new DateTimeObject($value);
        } catch (DateTimeObjectException $e) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->attribute,
                    'Unix timestamp, string or DateTimeObject',
                    gettype($value)
                ]
            );
        }

        return $this;
    }

    /**
     * Format attribute value
     *
     * @param $value
     *
     * @return int|null|string
     */
    private function formatValue($value)
    {
        if ($this->isNull($value)) {
            return null;
        }

        return (new DateTimeObject($value))->format($this->attributeFormat);
    }

    /**
     * Set default attribute value
     */
    private function setDefaultValueInternal()
    {
        $defaultValue = $this->defaultValue;
        if ($defaultValue == 'now') {
            $defaultValue = new DateTimeObject('now');
        }
        $this->setValue($defaultValue);
    }
}