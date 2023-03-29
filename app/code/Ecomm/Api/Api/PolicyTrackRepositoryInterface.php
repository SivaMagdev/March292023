<?php
 
namespace Ecomm\Api\Api;

use Ecomm\Api\Api\PolicyTrack\PolicyTrackInterface;

interface PolicyTrackRepositoryInterface
{
	/**
     * @param PolicyTrackInterface $data
     * @return Ecomm\Api\Api\PolicyTrackRepositoryInterface[]
     */
	public function save(PolicyTrackInterface $data);
}