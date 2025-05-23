<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Magento data selector form element
 */

namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date element
 */
class Date extends AbstractElement
{
    /**
     * @var \DateTime
     */
    protected $_value;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param TimezoneInterface $localeDate
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        TimezoneInterface $localeDate,
        $data = []
    ) {
        $this->localeDate = $localeDate;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('text');
        $this->setExtType('textfield');
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
    }

    /**
     * Check if a string is a date value
     *
     * @param string $value
     * @return bool
     */
    private function isDate(string $value): bool
    {
        $date = date_parse($value);

        return !empty($date['year']) && !empty($date['month']) && !empty($date['day']);
    }

    /**
     * Initial scope of method was to limit timestamp on x64 systems to mimic x32 systems,
     * but keeping the method for compatibility:
     * If script executes on x64 system, converts large numeric values to timestamp limit
     *
     * @param int $value
     * @return int
     */
    protected function _toTimestamp($value)
    {
        return $value;
    }

    /**
     * Set date value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        if (empty($value)) {
            $this->_value = '';
            return $this;
        }
        if ($value instanceof \DateTimeInterface) {
            $this->_value = $value;
            return $this;
        }
        try {
            if (preg_match('/^[\-]{0,1}[0-9]+$/', $value)) {
                $this->_value = (new \DateTime())->setTimestamp($this->_toTimestamp($value));
            } elseif (is_string($value) && $this->isDate($value)) {
                $this->_value = new \DateTime($value, new \DateTimeZone($this->localeDate->getConfigTimezone()));
            } else {
                $this->_value = '';
            }
        } catch (\Exception $e) {
            $this->_value = '';
        }
        return $this;
    }

    /**
     * Get date value as string.
     *
     * Format can be specified, or it will be taken from $this->getFormat()
     *
     * @param string $format (compatible with \DateTime)
     * @return string
     */
    public function getValue($format = null)
    {
        if (empty($this->_value)) {
            return '';
        }
        if (null === $format) {
            $format = $this->getDateFormat() ?: $this->getFormat();
            $format .= ($format && $this->getTimeFormat()) ? ' ' : '';
            $format .= $this->getTimeFormat() ? $this->getTimeFormat() : '';
        }
        return $this->localeDate->formatDateTime(
            $this->_value,
            null,
            null,
            null,
            $this->_value->getTimezone(),
            $format
        );
    }

    /**
     * Get value instance, if any
     *
     * @return \DateTime
     */
    public function getValueInstance()
    {
        if (empty($this->_value)) {
            return null;
        }
        return $this->_value;
    }

    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (\DateTime)
     * - output format must be set (compatible with \DateTime)
     *
     * @throws \Exception
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('admin__control-text input-text input-date');
        $dateFormat = $this->getDateFormat() ?: $this->getFormat();
        $timeFormat = $this->getTimeFormat();
        if (empty($dateFormat)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(
                'Output format is not specified. ' .
                'Please specify "format" key in constructor, or set it using setFormat().'
            );
        }

        $dataInit = 'data-mage-init="' . $this->_escape(
            json_encode(
                [
                    'calendar' => [
                        'dateFormat' => $dateFormat,
                        'showsTime' => !empty($timeFormat),
                        'timeFormat' => $timeFormat,
                        'buttonImage' => $this->getImage(),
                        'buttonText' => 'Select Date',
                        'disabled' => $this->getDisabled(),
                        'minDate' => $this->getMinDate(),
                        'maxDate' => $this->getMaxDate(),
                    ],
                ]
            )
        ) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
