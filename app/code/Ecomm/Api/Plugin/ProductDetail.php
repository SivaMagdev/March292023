<?php

namespace Ecomm\Api\Plugin;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Ecomm\Supportdocument\Helper\Output;

class ProductDetail
{
    protected $productExtensionFactory;
    protected $additionCollectionFactory;

    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        Output $supportdocumentHelper
    ) {
        $this->productExtensionFactory   = $productExtensionFactory;
        $this->supportdocumentHelper   = $supportdocumentHelper;
    }

    public function afterLoad(ProductModel $product)
    {
        $productExtension = $product->getExtensionAttributes();
        if (null === $productExtension) {
            $productExtension = $this->productExtensionFactory->create();
        }

        $supportdocument = $this->supportdocumentHelper->getAdditionalData($product->getId());
        $supportdocumentFinal = [];
        foreach ($supportdocument->getData() as $key => $supportdocument) {
            if($supportdocument['status'] == 1){
                $attachment = '';
                if($supportdocument['attachment'] != ''){
                    $attachment = $this->supportdocumentHelper->getAttachmentUrl($supportdocument['attachment']);
                }
                $supportdocumentFinal[$key]['is_logged_in'] = $supportdocument['is_logged_in'];
                $supportdocumentFinal[$key]['hide_leave_popup'] = $supportdocument['hide_leave_popup'];
                $supportdocumentFinal[$key]['link_title'] = $supportdocument['link_title'];
                $supportdocumentFinal[$key]['attachment'] = $attachment;
                $supportdocumentFinal[$key]['link']       = $supportdocument['link'];
            }
        }

        $productExtension->setSupportdocument($supportdocumentFinal);
        $productExtension->setLatexFree($product->getResource()->getAttribute('latex_free')->getFrontend()->getValue($product));
        $productExtension->setPreservativeFree($product->getResource()->getAttribute('preservative_free')->getFrontend()->getValue($product));
        $productExtension->setGlutenFree($product->getResource()->getAttribute('gluten_free')->getFrontend()->getValue($product));
        $productExtension->setDyeFree($product->getResource()->getAttribute('dye_free')->getFrontend()->getValue($product));
        $product->setExtensionAttributes($productExtension);

        return $product;
    }
}