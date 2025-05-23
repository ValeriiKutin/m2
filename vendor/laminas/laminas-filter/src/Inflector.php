<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Filter\FilterInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_key_exists;
use function array_keys;
use function array_shift;
use function array_values;
use function class_exists;
use function func_get_args;
use function is_array;
use function is_scalar;
use function is_string;
use function ltrim;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_replace;

/**
 * Filter chain for string inflection
 *
 * @psalm-type Options = array{
 *     target?: string,
 *     rules?: array,
 *     throwTargetExceptionsOn?: bool,
 *     targetReplacementIdentifier?: string,
 *     pluginManager?: FilterPluginManager,
 * }
 * @extends AbstractFilter<Options>
 * @final
 */
class Inflector extends AbstractFilter
{
    /** @var FilterPluginManager */
    protected $pluginManager;

    /** @var string */
    protected $target;

    /** @var bool */
    protected $throwTargetExceptionsOn = true;

    /** @var string */
    protected $targetReplacementIdentifier = ':';

    /** @var array */
    protected $rules = [];

    /**
     * @param string|array|Traversable $options Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (! is_array($options)) {
            $options = func_get_args();
            $temp    = [];

            if (! empty($options)) {
                $temp['target'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['rules'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['throwTargetExceptionsOn'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['targetReplacementIdentifier'] = array_shift($options);
            }

            $options = $temp;
        }

        $this->setOptions($options);
    }

    /**
     * Retrieve plugin manager
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement
     *
     * @return FilterPluginManager
     */
    public function getPluginManager()
    {
        if (! $this->pluginManager instanceof FilterPluginManager) {
            $this->setPluginManager(new FilterPluginManager(new ServiceManager()));
        }

        return $this->pluginManager;
    }

    /**
     * Set plugin manager
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement
     *
     * @return self
     */
    public function setPluginManager(FilterPluginManager $manager)
    {
        $this->pluginManager = $manager;
        return $this;
    }

    /**
     * Set options
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *             passed to the constructor.
     *
     * @param array|Options|iterable $options
     * @return self
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        // Set plugin manager
        if (array_key_exists('pluginManager', $options)) {
            if (is_scalar($options['pluginManager']) && class_exists($options['pluginManager'])) {
                $options['pluginManager'] = new $options['pluginManager']();
            }
            $this->setPluginManager($options['pluginManager']);
        }

        if (array_key_exists('throwTargetExceptionsOn', $options)) {
            $this->setThrowTargetExceptionsOn($options['throwTargetExceptionsOn']);
        }

        if (array_key_exists('targetReplacementIdentifier', $options)) {
            $this->setTargetReplacementIdentifier($options['targetReplacementIdentifier']);
        }

        if (array_key_exists('target', $options)) {
            $this->setTarget($options['target']);
        }

        if (array_key_exists('rules', $options)) {
            $this->addRules($options['rules']);
        }

        return $this;
    }

    /**
     * Set Whether or not the inflector should throw an exception when a replacement
     * identifier is still found within an inflected target.
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *              passed to the constructor.
     *
     * @param bool $throwTargetExceptionsOn
     * @return self
     */
    public function setThrowTargetExceptionsOn($throwTargetExceptionsOn)
    {
        $this->throwTargetExceptionsOn = (bool) $throwTargetExceptionsOn;
        return $this;
    }

    /**
     * Will exceptions be thrown?
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @return bool
     */
    public function isThrowTargetExceptionsOn()
    {
        return $this->throwTargetExceptionsOn;
    }

    /**
     * Set the Target Replacement Identifier, by default ':'
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *              passed to the constructor.
     *
     * @param string $targetReplacementIdentifier
     * @return self
     */
    public function setTargetReplacementIdentifier($targetReplacementIdentifier)
    {
        if ($targetReplacementIdentifier) {
            $this->targetReplacementIdentifier = (string) $targetReplacementIdentifier;
        }

        return $this;
    }

    /**
     * Get Target Replacement Identifier
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @return string
     */
    public function getTargetReplacementIdentifier()
    {
        return $this->targetReplacementIdentifier;
    }

    /**
     * Set a Target
     * ex: 'scripts/:controller/:action.:suffix'
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *              passed to the constructor.
     *
     * @param string $target
     * @return self
     */
    public function setTarget($target)
    {
        $this->target = (string) $target;
        return $this;
    }

    /**
     * Retrieve target
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set Target Reference
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @param string $target
     * @return self
     */
    public function setTargetReference(&$target)
    {
        $this->target = &$target;
        return $this;
    }

    /**
     * Is the same as calling addRules() with the exception that it
     * clears the rules before adding them.
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *             passed to the constructor.
     *
     * @return self
     */
    public function setRules(array $rules)
    {
        $this->clearRules();
        $this->addRules($rules);
        return $this;
    }

    /**
     * Multi-call to setting filter rules.
     *
     * If prefixed with a ":" (colon), a filter rule will be added.  If not
     * prefixed, a static replacement will be added.
     *
     * ex:
     * array(
     *     ':controller' => array('CamelCaseToUnderscore', 'StringToLower'),
     *     ':action'     => array('CamelCaseToUnderscore', 'StringToLower'),
     *     'suffix'      => 'phtml'
     *     );
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *              passed to the constructor.
     *
     * @return self
     */
    public function addRules(array $rules)
    {
        $keys = array_keys($rules);
        foreach ($keys as $spec) {
            if ($spec[0] === ':') {
                $this->addFilterRule($spec, $rules[$spec]);
            } else {
                $this->setStaticRule($spec, $rules[$spec]);
            }
        }

        return $this;
    }

    /**
     * Get rules
     *
     * By default, returns all rules. If a $spec is provided, will return those
     * rules if found, false otherwise.
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @param string $spec
     * @return array|false
     */
    public function getRules($spec = null)
    {
        if (null !== $spec) {
            $spec = $this->_normalizeSpec($spec);
            if (isset($this->rules[$spec])) {
                return $this->rules[$spec];
            }
            return false;
        }

        return $this->rules;
    }

    /**
     * Returns a rule set by setFilterRule(), a numeric index must be provided
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @param string $spec
     * @param int $index
     * @return FilterInterface|false
     */
    public function getRule($spec, $index)
    {
        $spec = $this->_normalizeSpec($spec);
        if (isset($this->rules[$spec]) && is_array($this->rules[$spec])) {
            if (isset($this->rules[$spec][$index])) {
                return $this->rules[$spec][$index];
            }
        }
        return false;
    }

    /**
     * Clears the rules currently in the inflector
     *
     * @deprecated Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @return self
     */
    public function clearRules()
    {
        $this->rules = [];
        return $this;
    }

    /**
     * Set a filtering rule for a spec.  $ruleSet can be a string, Filter object
     * or an array of strings or filter objects.
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *               passed to the constructor.
     *
     * @param  string $spec
     * @param array|string|FilterInterface $ruleSet
     * @return self
     */
    public function setFilterRule($spec, $ruleSet)
    {
        $spec               = $this->_normalizeSpec($spec);
        $this->rules[$spec] = [];
        return $this->addFilterRule($spec, $ruleSet);
    }

    /**
     * Add a filter rule for a spec
     *
     * @deprecated   Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *               passed to the constructor.
     *
     * @return self
     */
    public function addFilterRule(mixed $spec, mixed $ruleSet)
    {
        $spec = $this->_normalizeSpec($spec);
        if (! isset($this->rules[$spec])) {
            $this->rules[$spec] = [];
        }

        if (! is_array($ruleSet)) {
            $ruleSet = [$ruleSet];
        }

        if (is_string($this->rules[$spec])) {
            $temp                 = $this->rules[$spec];
            $this->rules[$spec]   = [];
            $this->rules[$spec][] = $temp;
        }

        foreach ($ruleSet as $rule) {
            $this->rules[$spec][] = $this->_getRule($rule);
        }

        return $this;
    }

    /**
     * Set a static rule for a spec.  This is a single string value
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement. Options should be
     *               passed to the constructor.
     *
     * @param  string $name
     * @param  string $value
     * @return self
     */
    public function setStaticRule($name, $value)
    {
        $name               = $this->_normalizeSpec($name);
        $this->rules[$name] = (string) $value;
        return $this;
    }

    /**
     * Set Static Rule Reference.
     *
     * This allows a consuming class to pass a property or variable
     * in to be referenced when its time to build the output string from the
     * target.
     *
     * @deprecated  Since 2.41.0 This method will be removed in version 3.0 without replacement.
     *
     * @param  string $name
     * @return self
     */
    public function setStaticRuleReference($name, mixed &$reference)
    {
        $name               = $this->_normalizeSpec($name);
        $this->rules[$name] = &$reference;
        return $this;
    }

    /**
     * Inflect
     *
     * @param  string|array $source
     * @throws Exception\RuntimeException
     * @return string
     */
    public function filter($source)
    {
        // clean source
        foreach ((array) $source as $sourceName => $sourceValue) {
            $source[ltrim($sourceName, ':')] = $sourceValue;
        }

        $pregQuotedTargetReplacementIdentifier = preg_quote($this->targetReplacementIdentifier, '#');
        $processedParts                        = [];

        foreach ($this->rules as $ruleName => $ruleValue) {
            if (isset($source[$ruleName])) {
                if (is_string($ruleValue)) {
                    // overriding the set rule
                    $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                        '\\',
                        '\\\\',
                        $source[$ruleName]
                    );
                } elseif (is_array($ruleValue)) {
                    $processedPart = $source[$ruleName];
                    foreach ($ruleValue as $ruleFilter) {
                        $processedPart = $ruleFilter($processedPart);
                    }
                    $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                        '\\',
                        '\\\\',
                        $processedPart
                    );
                }
            } elseif (is_string($ruleValue)) {
                $processedParts['#' . $pregQuotedTargetReplacementIdentifier . $ruleName . '#'] = str_replace(
                    '\\',
                    '\\\\',
                    $ruleValue
                );
            }
        }

        // all of the values of processedParts would have been str_replace('\\', '\\\\', ..)'d
        // to disable preg_replace backreferences
        $inflectedTarget = preg_replace(array_keys($processedParts), array_values($processedParts), $this->target);

        if (
            $this->throwTargetExceptionsOn
            && preg_match('#(?=' . $pregQuotedTargetReplacementIdentifier . '[A-Za-z]{1})#', $inflectedTarget)
        ) {
            throw new Exception\RuntimeException(
                'A replacement identifier ' . $this->targetReplacementIdentifier
                . ' was found inside the inflected target, perhaps a rule was not satisfied with a target source?  '
                . 'Unsatisfied inflected target: ' . $inflectedTarget
            );
        }

        return $inflectedTarget;
    }

    /**
     * Normalize spec string
     *
     * @param  string $spec
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _normalizeSpec($spec)
    {
        // @codingStandardsIgnoreEnd
        return ltrim((string) $spec, ':&');
    }

    /**
     * Resolve named filters and convert them to filter objects.
     *
     * @param  string $rule
     * @return FilterInterface|callable(mixed): mixed
     */
    // @codingStandardsIgnoreStart
    protected function _getRule($rule)
    {
        // @codingStandardsIgnoreEnd
        if ($rule instanceof FilterInterface) {
            return $rule;
        }

        $rule = (string) $rule;
        return $this->getPluginManager()->get($rule);
    }
}
