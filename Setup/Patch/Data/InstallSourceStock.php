<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MsiSourceStockSampleData\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class InstallSourceStock
 * @package Magento\MsiSourceStockSampleData\Setup\Patch\Data
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
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /** @var SearchCriteriaBuilder  */
    protected $searchCriteriaBuilder;

    /**
     * InstallSourceStock constructor.
     * @param SampleDataContext $sampleDataContext
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SourceInterfaceFactory $sourceInterface
     * @param StockInterfaceFactory $stockInterface
     * @param StockSourceLinkInterfaceFactory $stockSourceLinkInterface
     * @param SalesChannelInterfaceFactory $salesChannelInterface
     * @param StockRepositoryInterface $stockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        ModuleDataSetupInterface $moduleDataSetup,
        SourceInterfaceFactory $sourceInterface,
        StockInterfaceFactory $stockInterface,
        StockSourceLinkInterfaceFactory $stockSourceLinkInterface,
        SalesChannelInterfaceFactory $salesChannelInterface,
        StockRepositoryInterface $stockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder

       )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sourceInterface = $sourceInterface;
        $this->stockInterface = $stockInterface;
        $this->stockSourceLinkInterface = $stockSourceLinkInterface;
        $this->salesChannelInterface = $salesChannelInterface;
        $this->stockRepository = $stockRepository;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    public function apply()
    {
        //create sources
        $this->addSources(['Magento_MsiSourceStockSampleData::fixtures/sources.csv']);

        //create stock
        $this->addStocks(['Magento_MsiSourceStockSampleData::fixtures/stock.csv']);

        $this->assignSourcesToStock(['Magento_MsiSourceStockSampleData::fixtures/source_stock.csv']);

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

    public function addSources(array $fixtures): void
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $source = $this->sourceInterface->create();
                $source->setData(['source_code' => $data['source_code'],
                    'name' => $data['source_name'],
                    'contact_name' => $data['contact_name'],
                    'email' => $data['email'],
                    'enabled' => $data['enabled'],
                    'country_id' => $data['country_id'],
                    'phone' => $data['phone'],
                    'postcode' => $data['postcode']]);
                $source->save();
            }
        }
    }

    public function addStocks(array $fixtures): void
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $stock = $this->stockInterface->create();
                $stock->setName($data['stock_name']);
                $stock->save();
                //set sales channel on stock
                if($data['in_channel']){
                    $stockId = $stock->getStockId();
                    $stock = $this->stockRepository->get($stockId);
                    $extensionAttributes = $stock->getExtensionAttributes();
                    $salesChannel = $this->salesChannelInterface->create();
                    $salesChannel->setCode($data['in_channel']);
                    $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);
                    $salesChannels[] = $salesChannel;
                    $extensionAttributes->setSalesChannels($salesChannels);
                    $this->stockRepository->save($stock);
                }

            }
        }
    }

    public function assignSourcesToStock($fixtures): void
    {

        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                //assign sources to stock
                $this->searchCriteriaBuilder->addFilter('name', $data['stock'], 'eq');
                $search = $this->searchCriteriaBuilder->create();
                $stockList = $this->stockRepository->getList($search)->getItems();
                /** @var \Magento\InventoryApi\Api\Data\StockInterface $stock */
                foreach ($stockList as $stock) {

                    /** @var \Magento\InventoryApi\Api\Data\StockSourceLinkInterface $sourceLink */
                    $sourceLink = $this->stockSourceLinkInterface->create();
                    $sourceLink->setSourceCode($data['source_code']);
                    $sourceLink->setStockId($stock->getStockId());
                    $sourceLink->setPriority($data['priority']);
                    $sourceLink->save();
                }
            }
        }
    }
}
