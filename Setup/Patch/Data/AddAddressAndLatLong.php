<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MsiSourceStockSampleData\Setup\Patch\Data;


use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Directory\Model\RegionFactory;

class AddAddressAndLatLong implements DataPatchInterface
{


    /** @var \Magento\Framework\Setup\SampleData\FixtureManager  */
    protected $fixtureManager;

    /** @var SourceRepositoryInterface  */
    protected $sourceRepository;

    /** @var RegionFactory  */
    protected $region;


    public function __construct(SampleDataContext $sampleDataContext,
                                SourceRepositoryInterface $sourceRepository,
                                RegionFactory $region
                                )
    {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->sourceRepository = $sourceRepository;
        $this->region = $region;
    }

    public function apply()
    {
        //add addresses to sources
        $this->addAddresses(['Magento_MsiSourceStockSampleData::fixtures/sources.csv']);
    }


    public function addAddresses(array $fixtures): void
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
                //$region = $this->region->create()->loadByCode($data['region'],$data['country_id']);
               // echo "region id ". $data['region_id'] ."\n";
                $source = $this->sourceRepository->get($data['source_code']);
                $source->setLatitude((float) $data['lat']);
                $source->setLongitude((float) $data['long']);
                $source->setStreet($data['street']);
                $source->setCity($data['city']);
                $source->setPostcode($data['postcode']);
                if($data['region_id']!=''){
                    echo "region id ". $data['region_id'] ."\n";
                    $source->setRegionId($data['region_id']);
                }
                $source->setRegion($data['region']);
                $this->sourceRepository->save($source);

            }
        }
    }
    public static function getDependencies()
    {
        return [InstallSourceStock::class];
    }

    public function getAliases()
    {
        return [];
    }
}