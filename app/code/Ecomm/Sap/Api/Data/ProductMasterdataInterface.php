<?php

namespace Ecomm\Sap\Api\Data;

/**
 * @api
 */
interface ProductMasterdataInterface
{

    /**
     * Get article_code
     *
     * @return string
     */
    public function getArticleCode();

      /**
     * Set article_code
     *
     * @param string $article_code
     * @return $this
     */
    public function setArticleCode($article_code);

    /**
     * Get distribution_center_code
     *
     * @return string
     */
    public function getDistributionCenterCode();

      /**
     * Set distribution_center_code
     *
     * @param string $distribution_center_code
     * @return $this
     */
    public function setDistributionCenterCode($distribution_center_code);

}