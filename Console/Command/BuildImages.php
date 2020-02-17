<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 17.02.20
 * Time: 16:40
 */

namespace Perspective\CustomerAvatar\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Perspective\CustomerAvatar\Model\Image\Generate;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\Customer;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Exception;

class BuildImages extends Command
{
    /**
     * @var Generate
     */
    protected $_imageGenerator;

    /**
     * @var Collection
     */
    protected $_collection;

    /**
     * @var State
     */
    protected $_state;

    public function __construct(
        Generate $imageGenerator,
        Collection $collection,
        State $state,
        string $name = null
    )
    {
        $this->_state = $state;
        $this->_collection = $collection;
        $this->_imageGenerator = $imageGenerator;
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('customer:avatar:generate')
            ->setDescription('Generate avatar for customers');
    }

    /**
     * Execute builder
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->_state->setAreaCode(Area::AREA_ADMINHTML);
            $this->generate($output);
        } catch (Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }
    }

    /**
     * Generate images
     *
     * @param OutputInterface $output
     * @throws LocalizedException
     * @throws Exception
     */
    protected function generate(OutputInterface $output)
    {
        $generated = 0;
        $errors = 0;
        $collection = $this->getCollection();
        foreach ($collection->getItems() as $customer) {
            /** @var Customer $customer */
            if(!$customer->getData(AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED)) {
                try {
                    $customer->setData(
                        AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED,
                        $this->_imageGenerator->generateForCustomer($customer)
                    );
                    $customer->save();
                    $generated++;
                } catch (AvatarInvalidName $e) {
                    $name = $customer->getFirstName().' '.$customer->getLastName();
                    $output->writeln('<error>Invalid name for customer: '.$name.'</error>');
                    $output->writeln('<comment>Id: '.$customer->getId().'</comment>');
                    $errors++;
                }
            }
        }
        $size = $collection->getSize();
        $output->writeln(
            '<comment>Finished with: generated: '
            .$generated.' errors: '.$errors.' validated customers: '.$size.'</comment>'
        );
    }

    /**
     * Get customer's collection
     *
     * @return Collection
     * @throws LocalizedException
     */
    protected function getCollection()
    {
        $collection = $this->_collection;
        $collection->addAttributeToSelect(AvatarManagerInterface::ATTRIBUTE_CODE_GENERATED);
        return $collection;
    }
}
