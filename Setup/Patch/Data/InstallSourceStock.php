<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoEse\MsiSourceStockSampleData\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

/**
 * Class InstallConfigurableSampleData
 * @package Magento\ConfigurableSampleDataVenia\Setup\Patch\Data
 */
class InstallSourceStock implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface  */
    protected $moduleDataSetup;

    /** @var  SourceInterfaceFactory */
    protected $sourceInterface;

    /** @var StockInterfaceFactory  */
    protected $stockInterface;

    /** @var StockSourceLinkInterfaceFactory  */
    protected $stockSourceLinkInterface;

    /** @var SalesChannelInterface  */
    protected $salesChannelInterface;

    /** @var StockRepositoryInterface  */
    protected $stockRepository;


    /**
     * InstallSourceStock constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SourceInterfaceFactory $sourceInterface
     * @param StockInterfaceFactory $stockInterface
     * @param StockSourceLinkInterfaceFactory $stockSourceLinkInterface
     * @param SalesChannelInterfaceFactory $salesChannelInterface
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SourceInterfaceFactory $sourceInterface,
        StockInterfaceFactory $stockInterface,
        StockSourceLinkInterfaceFactory $stockSourceLinkInterface,
        SalesChannelInterfaceFactory $salesChannelInterface,
        StockRepositoryInterface $stockRepository
       )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sourceInterface = $sourceInterface;
        $this->stockInterface = $stockInterface;
        $this->stockSourceLinkInterface = $stockSourceLinkInterface;
        $this->salesChannelInterface = $salesChannelInterface;
        $this->stockRepository = $stockRepository;
    }


    public function apply()
    {
        //create sources
        $this->addSources();

        //create stock
        $this->addStocks();

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [//SetSession::class

        ];
    }

    public function addSources(): void
    {
        $source = $this->sourceInterface->create();
        $source->setData(['source_code' => 'source', 'name' => 'testname', 'contact_name' => 'Mr pink', 'email' => 'pink@thelumsatory.com',
            'enabled' => 1, 'country_id' => 'US', 'postcode' => '53094']);
        $source->save();
    }

    public function addStocks(): void
    {
        $stock = $this->stockInterface->create();
        $stock->setName('new Stock');
        $stock->save();
        $stockId = $stock->getStockId();
        //set sales channel on stock
        $stock = $this->stockRepository->get($stockId);
        $extensionAttributes = $stock->getExtensionAttributes();
        $salesChannel = $this->salesChannelInterface->create();
        $salesChannel->setCode('base');
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
        $salesChannels[] = $salesChannel;
        $extensionAttributes->setSalesChannels($salesChannels);
        $this->stockRepository->save($stock);
        //assign sources to stock
        /** @var \Magento\InventoryApi\Api\Data\StockSourceLinkInterface  $sourceLink */
        $sourceLink = $this->stockSourceLinkInterface->create();
        $sourceLink->setSourceCode('source');
        $sourceLink->setStockId(2);
        $sourceLink->setPriority(1);
        $sourceLink->save();

    }
}
