<?php
namespace Magento\Framework\App\Interception\Cache\CompiledConfig;

/**
 * Proxy class for @see \Magento\Framework\App\Interception\Cache\CompiledConfig
 */
class Proxy extends \Magento\Framework\App\Interception\Cache\CompiledConfig implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\App\Interception\Cache\CompiledConfig
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\Framework\\App\\Interception\\Cache\\CompiledConfig', $shared = true)
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['_subject', '_isShared', '_instanceName'];
    }

    /**
     * Retrieve ObjectManager from global scope
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     */
    public function __clone()
    {
        if ($this->_subject) {
            $this->_subject = clone $this->_getSubject();
        }
    }

    /**
     * Debug proxied instance
     */
    public function __debugInfo()
    {
        return ['i' => $this->_subject];
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\App\Interception\Cache\CompiledConfig
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->_getSubject()->getTag();
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->_getSubject()->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = 'all', array $tags = [])
    {
        return $this->_getSubject()->clean($mode, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        return $this->_getSubject()->test($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return $this->_getSubject()->load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return $this->_getSubject()->remove($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend()
    {
        return $this->_getSubject()->getBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->_getSubject()->getLowLevelFrontend();
    }
}
