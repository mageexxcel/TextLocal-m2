<?php
namespace Excellence\Sms\Api;

use Excellence\Sms\Model\VerificationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface VerificationRepositoryInterface 
{
    public function save(VerificationInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(VerificationInterface $page);

    public function deleteById($id);
}
