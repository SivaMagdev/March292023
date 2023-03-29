<?php
/**
 * PwC India
 *
 * @category Magento
 * @package  Ecomm_HinCron
 * @author   PwC India
 * @license  GNU General Public License ("GPL") v3.0
 */

namespace Ecomm\HinCron\Model\Config;

class Cronconfig extends \Magento\Framework\App\Config\Value
{
    private const CRON_STRING_PATH = 'crontab/hin_cron/jobs/drl_hin_validator/schedule/cron_expr';
    private const CRON_MODEL_PATH = 'crontab/hin_cron/jobs/drl_hin_validator /run/model';
    
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */

    protected $_configValueFactory;

    /**
     * @var mixed|string
     */

    protected $_runModelPath = '';

    /**
     * CronConfig1 constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Cron
     *
     * @return CronConfig1
     * @throws \Exception
     */

    public function afterSave()
    {
        $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/hincron.log');
        $logger = new  \Laminas\Log\Logger();
        $logger->addWriter($writer);

        $time = $this->getData('groups/configurable_cron/fields/time/value');
        $frequency = $this->getData('groups/configurable_cron/fields/frequency/value');
        $cronExprArray = [
            (int) $time[1], //Minute
            (int) $time[0], //Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        $cronExprString = join(' ', $cronExprArray);
        $logger->info('time '. $cronExprString);
        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
            $logger->info('working');
        } catch (\Exception $e) {
          
            $logger->info('not working');
        }
        return parent::afterSave();
    }
}