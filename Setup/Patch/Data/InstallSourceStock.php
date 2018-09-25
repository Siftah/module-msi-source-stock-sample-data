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

/**
 * Class InstallConfigurableSampleData
 * @package Magento\ConfigurableSampleDataVenia\Setup\Patch\Data
 */
class InstallSourceStock implements DataPatchInterface
{

    /** @var ModuleDataSetupInterface  */
    protected $moduleDataSetup;

    /** @var  SourceInterfaceFactory */
    protected $sourceInterfaceFactory;

    /** @var StockInterfaceFactory  */
    protected $stockInterfaceFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SourceInterfaceFactory $sourceInterfaceFactory,
        StockInterfaceFactory $stockInterfaceFactory
       )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
        $this->stockInterfaceFactory = $stockInterfaceFactory;

    }


    public function apply()
    {
        //create sources
       // $this->addSources();

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
        $source = $this->sourceInterfaceFactory->create();
        $source->setData(['source_code' => 'source', 'name' => 'testname', 'contact_name' => 'Mr pink', 'email' => 'pink@thelumsatory.com',
            'enabled' => 1, 'country_id' => 'US', 'postcode' => '53094']);
        $source->save();
    }

    public function addStocks(): void
    {
        $stock = $this->stockInterfaceFactory->create();
        $stock->setName('new Stock');
        $stock->save();
        //assign sources to stock
        //set sales channel
    }
}
